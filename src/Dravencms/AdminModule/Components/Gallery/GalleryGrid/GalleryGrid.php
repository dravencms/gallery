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

namespace Dravencms\AdminModule\Components\Gallery\GalleryGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\Locale;
use Nette\Security\User;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

/**
 * Description of GalleryGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class GalleryGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var GalleryRepository */
    private $galleryRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var User */
    private $user;

    /** @var Locale */
    private $currentLocale;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * GalleryGrid constructor.
     * @param GalleryRepository $galleryRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param User $user
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @throws \Exception
     */
    public function __construct(
        GalleryRepository $galleryRepository, 
        BaseGridFactory $baseGridFactory, 
        EntityManager $entityManager,
        User $user,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        $this->baseGridFactory = $baseGridFactory;
        $this->galleryRepository = $galleryRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
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

        $grid->setDataSource($this->galleryRepository->getGalleryQueryBuilder());

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'Identifier')
            ->setFilterText();

        $grid->addColumnDateTime('date', 'Date')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnDateTime('updatedAt', 'Last edit')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnBoolean('isActive', 'Active');

        $grid->addColumnNumber('position', 'Position')
            ->setAlign('center')
            ->setFilterRange();

        $grid->addColumnNumber('pictures', 'Pictures')
            ->setAlign('center')
            ->setRenderer(function($row){
                /** @var Gallery $row */
                return $row->getPictures()->count();
            });

        if ($this->user->isAllowed('gallery', 'edit'))
        {
            $grid->addAction('pictures', 'Pictures')
                ->setIcon('folder-open')
                ->setTitle('Pictures')
                ->setClass('btn btn-xs btn-default');

            $grid->addAction('edit', '')
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
     * @isAllowed(gallery, delete)
     */
    public function handleDelete($id): void
    {
        $galleries = $this->galleryRepository->getById($id);
        foreach ($galleries AS $gallery)
        {
            foreach($gallery->getPictures() AS $picture){
                $structureFileLink = $picture->getStructureFileLink();
                if ($structureFileLink) {
                    $structureFileLink->setIsUsed(false);
                    $structureFileLink->setIsAutoclean(true);
                    $this->entityManager->persist($structureFileLink);
                }

                $this->entityManager->remove($picture);
            }

            $this->entityManager->remove($gallery);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/GalleryGrid.latte');
        $template->render();
    }
}
