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

namespace Dravencms\AdminModule\Components\Gallery;

use Dravencms\Components\BaseFormFactory;
use App\Model\Gallery\Entities\Gallery;
use App\Model\Gallery\Repository\GalleryRepository;
use App\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of GalleryForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class GalleryForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var GalleryRepository */
    private $galleryRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Gallery|null */
    private $gallery = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * GalleryForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param GalleryRepository $galleryRepository
     * @param LocaleRepository $localeRepository
     * @param Gallery|null $gallery
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        GalleryRepository $galleryRepository,
        LocaleRepository $localeRepository,
        Gallery $gallery = null
    ) {
        parent::__construct();

        $this->gallery = $gallery;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->galleryRepository = $galleryRepository;
        $this->localeRepository = $localeRepository;


        if ($this->gallery) {
            $defaults = [
                /*'name' => $this->gallery->getName(),
                'description' => $this->gallery->getDescription(),*/
                'position' => $this->gallery->getPosition(),
                'isActive' => $this->gallery->isActive(),
                'isInOverview' => $this->gallery->isInOverview(),
                'isShowName' => $this->gallery->isShowName(),
            ];

            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaults += $repository->findTranslations($this->gallery);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale) {
                $defaults[$defaultLocale->getLanguageCode()]['name'] = $this->gallery->getName();
                $defaults[$defaultLocale->getLanguageCode()]['position'] = $this->gallery->getDescription();
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
        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->galleryRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->gallery)) {
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

        if ($this->gallery) {
            $gallery = $this->gallery;
            //$gallery->setName($values->name);
            //$gallery->setDescription($values->description);
            $gallery->setIsActive($values->isActive);
            $gallery->setIsInOverview($values->isInOverview);
            $gallery->setIsShowName($values->isShowName);
            $gallery->setPosition($values->position);
        } else {
            $defaultLocale = $this->localeRepository->getDefault();
            $gallery = new Gallery(
                $values->{$defaultLocale->getLanguageCode()}->name,
                $values->{$defaultLocale->getLanguageCode()}->description,
                $values->isActive, $values->isShowName, $values->isInOverview);
        }

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($gallery, 'name', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->name)
                ->translate($gallery, 'description', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->description);
        }

        $this->entityManager->persist($gallery);

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