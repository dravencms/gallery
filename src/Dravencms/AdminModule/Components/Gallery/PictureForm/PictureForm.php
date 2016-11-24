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

namespace Dravencms\AdminModule\Components\Gallery\PictureForm;

use Dravencms\Components\BaseFormFactory;

use Dravencms\File\File;
use Dravencms\Model\File\Repository\StructureFileRepository;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Repository\PictureRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Tag\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of PictureForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class PictureForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var PictureRepository */
    private $pictureRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Gallery */
    private $gallery;

    /** @var File */
    private $file;

    /** @var Picture|null */
    private $picture = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * PictureForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param PictureRepository $pictureRepository
     * @param TagRepository $tagRepository
     * @param StructureFileRepository $structureFileRepository
     * @param LocaleRepository $localeRepository
     * @param Gallery $gallery
     * @param File $file
     * @param Picture|null $picture
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        PictureRepository $pictureRepository,
        TagRepository $tagRepository,
        StructureFileRepository $structureFileRepository,
        LocaleRepository $localeRepository,
        Gallery $gallery,
        File $file,
        Picture $picture = null
    ) {
        parent::__construct();

        $this->gallery = $gallery;
        $this->picture = $picture;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->pictureRepository = $pictureRepository;
        $this->tagRepository = $tagRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->localeRepository = $localeRepository;
        $this->file = $file;


        if ($this->picture) {

            $tags = [];
            foreach($this->picture->getTags() AS $tag)
            {
                $tags[$tag->getId()] = $tag->getId();
            }

            $defaults = [
                /*'name' => $this->picture->getName(),
                'description' => $this->picture->getDescription(),*/
                'position' => $this->picture->getPosition(),
                'isActive' => $this->picture->isActive(),
                'isPrimary' => $this->picture->isPrimary(),
                'structureFile' => $this->picture->getStructureFile()->getId(),
                'tags' => $tags
            ];

            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaults += $repository->findTranslations($this->picture);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale) {
                $defaults[$defaultLocale->getLanguageCode()]['name'] = $this->picture->getName();
                $defaults[$defaultLocale->getLanguageCode()]['position'] = $this->picture->getDescription();
            }

        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('Please enter gallery name.')
                ->addRule(Form::MAX_LENGTH, 'Gallery name is too long.', 255);

            $container->addTextArea('description');
        }


        $form->addText('structureFile')
            ->setType('number')
            ->setRequired('Please select the photo.');

        $form->addMultiSelect('tags', null, $this->tagRepository->getPairs());
        
        $form->addText('position')
            ->setDisabled(is_null($this->picture));

        $form->addCheckbox('isActive');
        $form->addCheckbox('isPrimary');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->pictureRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->gallery, $this->picture)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->presenter->isAllowed('gallery', 'edit')) {
            $form->addError('Nemáte oprávění editovat article.');
        }
    }

    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        $tags = new ArrayCollection($this->tagRepository->getById($values->tags));
        $structureFile = $this->structureFileRepository->getOneById($values->structureFile);

        if ($this->picture) {
            $picture = $this->picture;
            //$picture->setName($values->name);
            //$picture->setDescription($values->description);
            $picture->setIsActive($values->isActive);
            $picture->setIsPrimary($values->isPrimary);
            $picture->setPosition($values->position);
            $picture->setStructureFile($structureFile);
        } else {
            $defaultLocale = $this->localeRepository->getDefault();
            $picture = new Picture($this->gallery, $structureFile, $values->{$defaultLocale->getLanguageCode()}->name, $values->{$defaultLocale->getLanguageCode()}->description, $values->isActive, $values->isPrimary);
        }
        $picture->setTags($tags);

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($picture, 'name', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->name)
                ->translate($picture, 'description', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->description);
        }

        $this->entityManager->persist($picture);

        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render()
    {
        $template = $this->template;
        $template->fileSelectorPath = $this->file->getFileSelectorPath();
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/PictureForm.latte');
        $template->render();
    }
}