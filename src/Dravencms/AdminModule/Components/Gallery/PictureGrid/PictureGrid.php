<?php declare(strict_types = 1);

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Gallery\PictureGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Repository\PictureRepository;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\Locale;
use Nette\Security\User;
use Nette\Utils\Html;
use Salamek\Files\ImagePipe;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

/**
 * Description of PictureGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class PictureGrid extends BaseControl
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var PictureRepository */
    private $pictureRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var User */
    private $user;

    /** @var Locale */
    private $currentLocale;

    /** @var ImagePipe */
    private $imagePipe;

    /** @var Gallery */
    private $gallery;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * PictureGrid constructor.
     * @param Gallery $gallery
     * @param PictureRepository $pictureRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param User $user
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param ImagePipe $imagePipe
     * @throws \Exception
     */
    public function __construct(
        Gallery $gallery,
        PictureRepository $pictureRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        User $user,
        CurrentLocaleResolver $currentLocaleResolver,
        ImagePipe $imagePipe
    )
    {
        $this->baseGridFactory = $baseGridFactory;
        $this->pictureRepository = $pictureRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->imagePipe = $imagePipe;
        $this->gallery = $gallery;
    }


    /**
     * @param string $name
     * @return Grid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->pictureRepository->getPictureQueryBuilder($this->gallery));

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'Identifier')
            ->setAlign('center')
            ->setRenderer(function ($row) use($grid){
                /** @var Picture $row */
                if ($haveImage = $row->getStructureFileLink()) {
                    $img = Html::el('img');
                    $img->src = $this->imagePipe->request($haveImage->getStructureFile()->getFile(), '200x');
                } else {
                    $img = '';
                }

                if ($row->isPrimary()) {
                    $el = Html::el('span', $grid->getTranslator()->translate('Primary photo'));
                    $el->class = 'label label-info';
                } else {
                    $el = '';
                }

                $container = Html::el('div');
                $container->addHtml($el);
                $container->addHtml('<br>');
                $container->addHtml($img);
                $container->addHtml('<br>');
                $container->addText($row->getIdentifier());

                return $container;
            })
            ->setFilterText();


        $grid->addColumnDateTime('updatedAt', 'Last edit')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnBoolean('isActive', 'Active');

        $grid->addColumnNumber('position', 'Position')
            ->setAlign('center')
            ->setFilterRange();

        if ($this->user->isAllowed('gallery', 'edit'))
        {
            $grid->addAction('editPicture', '', 'editPicture', ['galleryId' => 'gallery.id', 'pictureId' => 'id'])
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed('gallery', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirmation(new StringConfirmation('Do you really want to delete row %s?', 'identifier'));

            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDelete'];
        }

        $grid->addExportCsvFiltered('Csv export (filtered)', 'acl_resource_filtered.csv')
            ->setTitle('Csv export (filtered)');

        $grid->addExportCsv('Csv export', 'acl_resource_all.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id): void
    {
        $pictures = $this->pictureRepository->getById($id);
        foreach ($pictures AS $picture)
        {
            $structureFileLink = $picture->getStructureFileLink();
            if ($structureFileLink) {
                $structureFileLink->setIsUsed(false);
                $structureFileLink->setIsAutoclean(true);
                $this->entityManager->persist($structureFileLink);
            }

            $this->entityManager->remove($picture);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/PictureGrid.latte');
        $template->render();
    }
}
