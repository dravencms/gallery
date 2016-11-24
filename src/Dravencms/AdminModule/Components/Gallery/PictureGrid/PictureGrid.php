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

namespace Dravencms\AdminModule\Components\Gallery\PictureGrid;

use Dravencms\Components\BaseGridFactory;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Repository\PictureRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Utils\Html;
use Salamek\Files\ImagePipe;

/**
 * Description of PictureGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class PictureGrid extends Control
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var PictureRepository */
    private $pictureRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var LocaleRepository */
    private $localeRepository;

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
     * @param LocaleRepository $localeRepository
     */
    public function __construct(Gallery $gallery, PictureRepository $pictureRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager, LocaleRepository $localeRepository, ImagePipe $imagePipe)
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->pictureRepository = $pictureRepository;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeRepository;
        $this->imagePipe = $imagePipe;
        $this->gallery = $gallery;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->pictureRepository->getPictureQueryBuilder($this->gallery));

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('name', 'Name')
            ->setCustomRender(function ($row) use($grid){
                /** @var Picture $row */
                if ($haveImage = $row->getStructureFile()) {
                    $img = Html::el('img');
                    $img->src = $this->imagePipe->request($haveImage->getFile(), '200x');
                } else {
                    $img = '';
                }

                if ($row->isPrimary()) {
                    $el = Html::el('span', $grid->getTranslator()->translate('Primary photo'));
                    $el->class = 'label label-info';
                } else {
                    $el = '';
                }

                return $el . Html::el('br') . $img . Html::el('br') . $row->getName();
            })
            ->setFilterText()
            ->setSuggestion();

        $grid->getColumn('name')->cellPrototype->class[] = 'center';

        $grid->addColumnDate('updatedAt', 'Last edit', $this->localeRepository->getLocalizedDateTimeFormat())
            ->setSortable()
            ->setFilterDate();
        $grid->getColumn('updatedAt')->cellPrototype->class[] = 'center';

        $grid->addColumnBoolean('isActive', 'Active');

        $grid->addColumnNumber('position', 'Position')
            ->setFilterNumber()
            ->setSuggestion();

        $grid->getColumn('position')->cellPrototype->class[] = 'center';

        if ($this->presenter->isAllowed('gallery', 'edit')) {
            $grid->addActionHref('editPicture', 'Upravit')
                ->setCustomHref(function($row){
                    return $this->presenter->link('editPicture', ['galleryId' => $row->getGallery()->getId(), 'pictureId' => $row->getId()]);
                })
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
        $pictures = $this->pictureRepository->getById($id);
        foreach ($pictures AS $picture)
        {
            $this->entityManager->remove($picture);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/PictureGrid.latte');
        $template->render();
    }
}
