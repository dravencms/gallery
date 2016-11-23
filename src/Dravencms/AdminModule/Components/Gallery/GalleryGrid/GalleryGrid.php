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

use Dravencms\Components\BaseGridFactory;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use App\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

/**
 * Description of GalleryGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class GalleryGrid extends Control
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var GalleryRepository */
    private $galleryRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var LocaleRepository */
    private $localeRepository;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * GalleryGrid constructor.
     * @param GalleryRepository $galleryRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(GalleryRepository $galleryRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager, LocaleRepository $localeRepository)
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->galleryRepository = $galleryRepository;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeRepository;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->galleryRepository->getGalleryQueryBuilder());

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('name', 'Name')
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnDate('updatedAt', 'Last edit', $this->localeRepository->getLocalizedDateTimeFormat())
            ->setSortable()
            ->setFilterDate();
        $grid->getColumn('updatedAt')->cellPrototype->class[] = 'center';

        $grid->addColumnBoolean('isActive', 'Active');

        $grid->addColumnNumber('position', 'Position')
            ->setFilterNumber()
            ->setSuggestion();

        $grid->getColumn('position')->cellPrototype->class[] = 'center';

        $grid->addColumnNumber('pictures', 'Pictures')
            ->setCustomRender(function($row){
                /** @var Gallery $row */
                return $row->getPictures()->count();
            });

        $grid->getColumn('pictures')->cellPrototype->class[] = 'center';

        if ($this->presenter->isAllowed('gallery', 'edit')) {
            $grid->addActionHref('pictures', 'Pictures')
                ->setIcon('folder-open');

            $grid->addActionHref('edit', 'Upravit')
                ->setIcon('pencil');
        }

        if ($this->presenter->isAllowed('gallery', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat město %s ?', $row->name];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i locales ?');
        }
        $grid->setExport();

        return $grid;
    }

    /**
     * @param $action
     * @param $ids
     */
    public function gridOperationsHandler($action, $ids)
    {
        switch ($action)
        {
            case 'delete':
                $this->handleDelete($ids);
                break;
        }
    }

    /**
     * @param $id
     * @throws \Exception
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
