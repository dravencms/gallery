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

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
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
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Salamek\Files\Models\IFile;

/**
 * Description of DirectoryPictureForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class DirectoryPictureForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var PictureRepository */
    private $pictureRepository;

    /** @var PictureTranslationRepository */
    private $pictureTranslationRepository;

    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var StructureRepository */
    private $structureRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Gallery */
    private $gallery;

    /** @var array */
    public $onSuccess = [];

    /**
     * DirectoryPictureForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param PictureRepository $pictureRepository
     * @param PictureTranslationRepository $pictureTranslationRepository
     * @param TagRepository $tagRepository
     * @param StructureFileRepository $structureFileRepository
     * @param StructureRepository $structureRepository
     * @param LocaleRepository $localeRepository
     * @param Gallery $gallery
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        PictureRepository $pictureRepository,
        PictureTranslationRepository $pictureTranslationRepository,
        TagRepository $tagRepository,
        StructureFileRepository $structureFileRepository,
        StructureRepository $structureRepository,
        LocaleRepository $localeRepository,
        Gallery $gallery
    ) {
        parent::__construct();

        $this->gallery = $gallery;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->pictureRepository = $pictureRepository;
        $this->tagRepository = $tagRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->pictureTranslationRepository = $pictureTranslationRepository;
        $this->structureRepository = $structureRepository;
        $this->localeRepository = $localeRepository;

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
            //Ignore non image files
            if ($structureFile->getFile()->getType() != IFile::TYPE_IMAGE)
            {
                continue;
            }
            
            $name = $structureFile->getId().'-'.$structureFile->getName();
            $identifier = md5($name.microtime().rand().$structureFile->getFile()->getSum());
            
            $picture = new Picture($this->gallery, $structureFile, $identifier, $values->isActive, false);
            $picture->setTags($tags);

            $this->entityManager->persist($picture);
            $this->entityManager->flush();

            foreach ($this->localeRepository->getActive() AS $activeLocale) {
                if ($pictureTranslation = $this->pictureTranslationRepository->getTranslation($picture, $activeLocale))
                {
                    $pictureTranslation->setName($identifier);
                }
                else
                {
                    $pictureTranslation = new PictureTranslation(
                        $picture,
                        $activeLocale,
                        $name
                    );
                }

                $this->entityManager->persist($pictureTranslation);
            }
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
