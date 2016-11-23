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

namespace Dravencms\AdminModule\Components\Gallery\DirectoryPictureForm;

use Dravencms\Components\BaseFormFactory;
use App\Model\File\Repository\StructureFileRepository;
use App\Model\File\Repository\StructureRepository;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Repository\PictureRepository;
use App\Model\Tag\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of DirectoryPictureForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class DirectoryPictureForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var PictureRepository */
    private $pictureRepository;

    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var StructureRepository */
    private $structureRepository;

    /** @var Gallery */
    private $gallery;

    /** @var array */
    public $onSuccess = [];

    /**
     * DirectoryPictureForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param PictureRepository $pictureRepository
     * @param TagRepository $tagRepository
     * @param StructureFileRepository $structureFileRepository
     * @param StructureRepository $structureRepository
     * @param Gallery $gallery
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        PictureRepository $pictureRepository,
        TagRepository $tagRepository,
        StructureFileRepository $structureFileRepository,
        StructureRepository $structureRepository,
        Gallery $gallery
    ) {
        parent::__construct();

        $this->gallery = $gallery;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->pictureRepository = $pictureRepository;
        $this->tagRepository = $tagRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->structureRepository = $structureRepository;

        $this['form']->setDefaults(['isActive' => true]);
    }

    /**
     * @return array
     */
    private function buildPaths()
    {
        $return = [];
        foreach ($this->structureRepository->getAll() AS $item)
        {
            $path = [];
            $breadcrumbs = $this->structureRepository->buildParentTree($item);
            foreach($breadcrumbs AS $breadcrumb)
            {
                $path[] = $breadcrumb->getName();
            }

            $return[$item->getId()] = implode('/', $path);
        }

        return $return;
    }

    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addSelect('structure', null, $this->buildPaths());

        $form->addMultiSelect('tags', null, $this->tagRepository->getPairs());

        $form->addCheckbox('isActive');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();
        if (!$this->presenter->isAllowed('gallery', 'edit')) {
            $form->addError('Nemáte oprávění editovat gallery.');
        }
    }

    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        $tags = new ArrayCollection($this->tagRepository->getById($values->tags));

        $structure = $this->structureRepository->getOneById($values->structure);
        foreach ($structure->getStructureFiles() AS $structureFile)
        {
            $picture = new Picture($this->gallery, $structureFile, $structureFile->getId().'-'.$structureFile->getName(), '', $values->isActive, false);
            $picture->setTags($tags);

            $this->entityManager->persist($picture);
        }
        
        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/DirectoryPictureForm.latte');
        $template->render();
    }
}