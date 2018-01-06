<?php

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
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;

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

    /** @var ILocale */
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
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function __construct(
        GalleryRepository $galleryRepository, 
        BaseGridFactory $baseGridFactory, 
        EntityManager $entityManager,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->galleryRepository = $galleryRepository;
        $this->entityManager = $entityManager;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }


    /**
     * @param $name
     * @return Grid
     * @throws \Ublaboo\DataGrid\Exception\DataGridColumnNotFoundException
     */
    public function createComponentGrid($name)
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->galleryRepository->getGalleryQueryBuilder());

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'Identifier')
            ->setFilterText();

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

        if ($this->presenter->isAllowed('gallery', 'edit'))
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

        if ($this->presenter->isAllowed('gallery', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'identifier');

            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        $grid->addExportCsvFiltered('Csv export (filtered)', 'acl_resource_filtered.csv')
            ->setTitle('Csv export (filtered)');

        $grid->addExportCsv('Csv export', 'acl_resource_all.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param array $ids
     */
    public function gridGroupActionDelete(array $ids)
    {
        $this->handleDelete($ids);
    }

    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(gallery, delete)
     */
    public function handleDelete($id)
    {
        $locales = $this->galleryRepository->getById($id);
        foreach ($locales AS $locale)
        {
            $this->entityManager->remove($locale);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/GalleryGrid.latte');
        $template->render();
    }
}
