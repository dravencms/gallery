<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\GalleryModule;

use Dravencms\AdminModule\Components\Gallery\DirectoryPictureForm\DirectoryPictureFormFactory;
use Dravencms\AdminModule\Components\Gallery\GalleryForm\GalleryFormFactory;
use Dravencms\AdminModule\Components\Gallery\GalleryGrid\GalleryGridFactory;
use Dravencms\AdminModule\Components\Gallery\PictureForm\PictureFormFactory;
use Dravencms\AdminModule\Components\Gallery\PictureGrid\PictureGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Dravencms\Model\Gallery\Repository\PictureRepository;
use Dravencms\Model\Tag\Repository\TagRepository;

/**
 * Description of GalleryPresenter
 *
 * @author sadam
 */
class GalleryPresenter extends SecuredPresenter
{
    /** @var GalleryRepository @inject */
    public $galleryRepository;

    /** @var PictureRepository @inject */
    public $pictureRepository;
    
    /** @var TagRepository @inject */
    public $tagRepository;

    /** @var GalleryFormFactory @inject */
    public $galleryFormFactory;

    /** @var GalleryGridFactory @inject */
    public $galleryGridFactory;

    /** @var PictureFormFactory @inject */
    public $pictureFormFactory;

    /** @var PictureGridFactory @inject */
    public $pictureGridFactory;
    
    /** @var DirectoryPictureFormFactory @inject */
    public $directoryPictureFormFactory;

    /** @var null|Gallery */
    private $gallery = null;

    /** @var null|Picture */
    private $picture = null;

    /**
     * @isAllowed(gallery,edit)
     */
    public function renderDefault()
    {
        $this->template->h1 = 'Galleries';
    }

    /**
     * @isAllowed(gallery,edit)
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit($id)
    {
        if ($id) {
            $this->template->h1 = 'Edit gallery';
            $gallery = $this->galleryRepository->getOneById($id);
            if (!$gallery) {
                $this->error();
            }
            $this->gallery = $gallery;
        } else {
            $this->template->h1 = 'New gallery';
        }
    }

    /**
     * @param $galleryId
     * @param null $pictureId
     */
    public function actionEditPicture($galleryId, $pictureId = null)
    {
        $this->gallery = $this->galleryRepository->getOneById($galleryId);
        if ($pictureId)
        {
            $picture = $this->pictureRepository->getOneById($pictureId);
            if (!$picture) {
                $this->error();
            }

            $this->picture = $picture;
            $this->template->h1 = 'Edit picture';
        }
        else
        {
            $this->template->h1 = 'New picture';
        }
    }

    /**
     * @param $galleryId
     */
    public function actionNewPictureFromDirectory($galleryId)
    {
        $this->gallery = $this->galleryRepository->getOneById($galleryId);
        $this->template->h1 = 'New pictures from directory into gallery: '. $this->gallery->getIdentifier();
    }

    /**
     * @param $id
     */
    public function actionPictures($id)
    {
        $this->gallery = $this->galleryRepository->getOneById($id);
        $this->template->gallery = $this->gallery;
        $this->template->h1 = 'Photos';
    }

    /**
     * @return \AdminModule\Components\Gallery\GalleryForm
     */
    public function createComponentFormGallery()
    {
        $control = $this->galleryFormFactory->create($this->gallery);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Gallery has been successfully saved', 'alert-success');
            $this->redirect("Gallery:");
        };

        return $control;
    }

    /**
     * @return \AdminModule\Components\Gallery\PictureForm
     */
    public function createComponentFormPicture()
    {
        $control = $this->pictureFormFactory->create($this->gallery, $this->picture);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Picture has been successfully saved', 'alert-success');
            $this->redirect('Gallery:pictures', $this->gallery->getId());
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Gallery\GalleryGrid
     */
    public function createComponentGridGallery()
    {
        $control = $this->galleryGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Gallery has been successfully deleted', 'alert-success');
            $this->redirect('Gallery:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Gallery\PictureGrid
     */
    public function createComponentGridPicture()
    {
        $control = $this->pictureGridFactory->create($this->gallery);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Picture has been successfully deleted', 'alert-success');
            $this->redirect('Gallery:pictures', $this->gallery->getId());
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Gallery\DirectoryPictureForm
     */
    public function createComponentFormDirectoryPicture()
    {
        $control = $this->directoryPictureFormFactory->create($this->gallery);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Pictures has been successfully saved', 'alert-success');
            $this->redirect('Gallery:pictures', $this->gallery->getId());
        };
        return $control;
    }
}
