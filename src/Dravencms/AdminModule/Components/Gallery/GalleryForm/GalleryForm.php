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

namespace Dravencms\AdminModule\Components\Gallery\GalleryForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\GalleryTranslation;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Dravencms\Model\Gallery\Repository\GalleryTranslationRepository;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;

/**
 * Description of GalleryForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class GalleryForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var GalleryRepository */
    private $galleryRepository;

    /** @var GalleryTranslationRepository */
    private $galleryTranslationRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var \Dravencms\Model\Locale\Entities\Locale|null */
    private $currentLocale;

    /** @var Gallery|null */
    private $gallery = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * GalleryForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param GalleryRepository $galleryRepository
     * @param GalleryTranslationRepository $galleryTranslationRepository
     * @param LocaleRepository $localeRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param Gallery|null $gallery
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        GalleryRepository $galleryRepository,
        GalleryTranslationRepository $galleryTranslationRepository,
        LocaleRepository $localeRepository,
        CurrentLocaleResolver $currentLocaleResolver,
        Gallery $gallery = null
    ) {
        parent::__construct();

        $this->gallery = $gallery;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->galleryRepository = $galleryRepository;
        $this->galleryTranslationRepository = $galleryTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->localeRepository = $localeRepository;


        if ($this->gallery) {
            $defaults = [
                'position' => $this->gallery->getPosition(),
                'identifier' => $this->gallery->getIdentifier(),
                'isActive' => $this->gallery->isActive(),
                'isInOverview' => $this->gallery->isInOverview(),
                'isShowName' => $this->gallery->isShowName(),
                'date' => ($this->gallery->getDate() ? $this->gallery->getDate()->format($this->currentLocale->getDateFormat()) : null),
            ];

            foreach ($this->gallery->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
                $defaults[$translation->getLocale()->getLanguageCode()]['description'] = $translation->getDescription();
            }
        }
        else{
            $defaults = [
                'isActive' => true,
                'isShowName' => false,
                'isInOverview' => true
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

        $form->addText('date');

        $form->addText('identifier')
            ->setRequired('Please enter identifier');

        $form->addText('position')
            ->setDisabled(is_null($this->gallery));

        $form->addCheckbox('isActive');
        $form->addCheckbox('isShowName');
        $form->addCheckbox('isInOverview');


        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();

        if (!$this->galleryRepository->isIdentifierFree($values->identifier, $this->gallery)) {
            $form->addError('Tento identifier je již zabrán.');
        }

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->galleryTranslationRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->gallery)) {
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

        $date = ($values->date ? \DateTime::createFromFormat($this->currentLocale->getDateFormat(), $values->date) : null);
        
        if ($this->gallery) {
            $gallery = $this->gallery;
            $gallery->setIdentifier($values->identifier);
            $gallery->setIsActive($values->isActive);
            $gallery->setIsInOverview($values->isInOverview);
            $gallery->setIsShowName($values->isShowName);
            $gallery->setPosition($values->position);
            $gallery->setDate($date);
        } else {
            $gallery = new Gallery(
                $values->identifier,
                $date,
                $values->isActive,
                $values->isShowName,
                $values->isInOverview
            );
        }

        $this->entityManager->persist($gallery);

        $this->entityManager->flush();


        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($galleryTranslation = $this->galleryTranslationRepository->getTranslation($gallery, $activeLocale))
            {
                $galleryTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
                $galleryTranslation->setDescription($values->{$activeLocale->getLanguageCode()}->description);
            }
            else
            {
                $galleryTranslation = new GalleryTranslation(
                    $gallery,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name,
                    $values->{$activeLocale->getLanguageCode()}->description
                );
            }

            $this->entityManager->persist($galleryTranslation);
        }

        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render()
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/GalleryForm.latte');
        $template->render();
    }
}