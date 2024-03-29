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

namespace Dravencms\AdminModule\Components\Gallery\PictureForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;

use Dravencms\File\File;
use Dravencms\Model\File\Entities\Structure;
use Dravencms\Model\File\Entities\StructureFileLink;
use Dravencms\Model\File\Repository\StructureFileRepository;
use Dravencms\Model\File\Repository\StructureRepository;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Entities\PictureTranslation;
use Dravencms\Model\Gallery\Repository\PictureRepository;
use Dravencms\Model\Gallery\Repository\PictureTranslationRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Tag\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Dravencms\Database\EntityManager;
use Dravencms\Components\BaseForm\Form;
use Nette\Security\User;
use Salamek\Files\FileStorage;

/**
 * Description of PictureForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class PictureForm extends BaseControl
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

    /** @var PictureTranslationRepository */
    private $pictureTranslationRepository;

    /** @var StructureRepository */
    private $structureRepository;

    /** @var User */
    private $user;

    /** @var Gallery */
    private $gallery;

    /** @var File */
    private $file;

    /** @var FileStorage */
    private $fileStorage;

    /** @var Picture|null */
    private $picture = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * PictureForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param PictureRepository $pictureRepository
     * @param PictureTranslationRepository $pictureTranslationRepository
     * @param TagRepository $tagRepository
     * @param StructureFileRepository $structureFileRepository
     * @param StructureRepository $structureRepository
     * @param FileStorage $fileStorage
     * @param User $user
     * @param LocaleRepository $localeRepository
     * @param Gallery $gallery
     * @param File $file
     * @param Picture|null $picture
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        PictureRepository $pictureRepository,
        PictureTranslationRepository $pictureTranslationRepository,
        TagRepository $tagRepository,
        StructureFileRepository $structureFileRepository,
        StructureRepository $structureRepository,
        FileStorage $fileStorage,
        User $user,
        LocaleRepository $localeRepository,
        Gallery $gallery,
        File $file,
        Picture $picture = null
    ) {
        $this->gallery = $gallery;
        $this->picture = $picture;

        $this->baseFormFactory = $baseFormFactory;
        $this->pictureTranslationRepository = $pictureTranslationRepository;
        $this->entityManager = $entityManager;
        $this->pictureRepository = $pictureRepository;
        $this->tagRepository = $tagRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->localeRepository = $localeRepository;
        $this->structureRepository = $structureRepository;
        $this->fileStorage = $fileStorage;
        $this->user = $user;
        $this->file = $file;


        if ($this->picture) {

            $tags = [];
            foreach($this->picture->getTags() AS $tag)
            {
                $tags[$tag->getId()] = $tag->getId();
            }

            $defaults = [
                'position' => $this->picture->getPosition(),
                'identifier' => $this->picture->getIdentifier(),
                'isActive' => $this->picture->isActive(),
                'isPrimary' => $this->picture->isPrimary(),
                'structureFile' => $this->picture->getStructureFile()->getId(),
                'tags' => $tags
            ];

            foreach ($this->picture->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
                $defaults[$translation->getLocale()->getLanguageCode()]['description'] = $translation->getDescription();
            }

        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('Please enter picture name.')
                ->addRule(Form::MAX_LENGTH, 'Picture name is too long.', 255);

            $container->addTextArea('description');
        }

        $form->addText('identifier')
            ->setRequired('Please enter picture identifier.');


        $form->addText('structureFile')
            ->setHtmlType('number');

        $form->addUpload('file');

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

    public function editFormValidate(Form $form): void
    {
        $values = $form->getValues();

        if (!$this->pictureRepository->isIdentifierFree($values->identifier, $this->gallery, $this->picture)) {
            $form->addError('Tento identifier je již zabrán.');
        }

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->pictureTranslationRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->gallery, $this->picture)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->user->isAllowed('gallery', 'edit')) {
            $form->addError('Nemáte oprávění editovat gallery.');
        }
    }

    public function editFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        $tags = new ArrayCollection($this->tagRepository->getById($values->tags));

        if ($values->structureFile) {
            $structureFile = $this->structureFileRepository->getOneById(intval($values->structureFile));
        } else {
            $structureFile = null;
        }

        $file = $values->file;
        if ($file->isOk()) {
            $structure = $this->structureRepository->getOneByName(\Dravencms\Gallery\Gallery::PLUGIN_NAME);
            $structureFile = $this->fileStorage->processFile($file, $structure);
        }

        if ($this->picture) {
            $picture = $this->picture;
            $picture->setIdentifier($values->identifier);
            $picture->setIsActive($values->isActive);
            $picture->setIsPrimary($values->isPrimary);
            $picture->setPosition(intval($values->position));

            if ($picture->getStructureFileLink()) {
                $existingStructureFile = $picture->getStructureFileLink();
                $existingStructureFile->setStructureFile($structureFile);

            } else {
                $existingStructureFile = new StructureFileLink(\Dravencms\Gallery\Gallery::PLUGIN_NAME, $structureFile, true, true);
            }

            $this->entityManager->persist($existingStructureFile);
        } else {
            $structureFileLink = new StructureFileLink(\Dravencms\Gallery\Gallery::PLUGIN_NAME, $structureFile, true, true);
            $this->entityManager->persist($structureFileLink);
            $picture = new Picture($this->gallery, $structureFileLink, $values->identifier, $values->isActive, $values->isPrimary);
        }
        $picture->setTags($tags);

        $this->entityManager->persist($picture);

        $this->entityManager->flush();

        
        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($pictureTranslation = $this->pictureTranslationRepository->getTranslation($picture, $activeLocale))
            {
                $pictureTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
                $pictureTranslation->setDescription($values->{$activeLocale->getLanguageCode()}->description);
            }
            else
            {
                $pictureTranslation = new PictureTranslation(
                    $picture,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name,
                    $values->{$activeLocale->getLanguageCode()}->description
                );
            }

            $this->entityManager->persist($pictureTranslation);
        }

        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render(): void
    {
        $template = $this->template;
        $template->fileSelectorPath = $this->file->getFileSelectorPath();
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/PictureForm.latte');
        $template->render();
    }
}
