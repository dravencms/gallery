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
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;

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
     * @throws \Contributte\Datagrid\Exception\DataGridException
     */
    public function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);
        $grid->setWritePermission('gallery', 'edit');

        $grid->setDataSource($this->galleryRepository->getGalleryQueryBuilder());

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'gallery.admin.field.identifier')
            ->setFilterText();

        $grid->addColumnDateTime('date', 'gallery.admin.field.date')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnDateTime('updatedAt', 'gallery.admin.field.updatedAt')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnBoolean('isActive', 'gallery.admin.field.active');

        $grid->addColumnNumber('position', 'gallery.admin.field.position')
            ->setAlign('center')
            ->setFilterRange();

        $grid->addColumnNumber('pictures', 'gallery.admin.field.pictures')
            ->setAlign('center')
            ->setRenderer(function($row){
                /** @var Gallery $row */
                return $row->getPictures()->count();
            });

        if ($this->user->isAllowed('gallery', 'edit'))
        {
            $grid->addAction('pictures', 'gallery.admin.field.pictures')
                ->setIcon('folder-open')
                ->setTitle('gallery.admin.field.pictures')
                ->setClass('btn btn-sm btn-outline-secondary');

            $grid->addAction('edit', '')
                ->setIcon('pencil')
                ->setTitle('gallery.admin.action.edit')
                ->setClass('btn btn-sm btn-primary');
        }

        if ($this->user->isAllowed('gallery', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('gallery.admin.action.delete')
                ->setClass('btn btn-sm btn-danger ajax')
                ->setConfirmation(new StringConfirmation('gallery.admin.confirm.delete', 'identifier'));

            $grid->addGroupAction('gallery.admin.action.delete')->onSelect[] = [$this, 'handleDelete'];
        }

        $grid->addExportCsvFiltered('gallery.admin.action.exportFiltered', 'acl_resource_filtered.csv')
            ->setTitle('gallery.admin.action.exportFiltered');

        $grid->addExportCsv('gallery.admin.action.exportAll', 'acl_resource_all.csv')
            ->setTitle('gallery.admin.action.exportAll');

        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    #[\Dravencms\User\Attributes\IsAllowed('gallery', 'delete')]
    #[\Dravencms\Security\Mutation]
    public function handleDelete($id): void
    {
        $this->requirePermission('gallery', 'delete');
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
