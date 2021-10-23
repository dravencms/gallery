<?php declare(strict_types = 1);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\GalleryModule;

use Dravencms\AdminModule\Components\Gallery\DirectoryPictureForm\DirectoryPictureForm;
use Dravencms\AdminModule\Components\Gallery\DirectoryPictureForm\DirectoryPictureFormFactory;
use Dravencms\AdminModule\Components\Gallery\GalleryForm\GalleryForm;
use Dravencms\AdminModule\Components\Gallery\GalleryForm\GalleryFormFactory;
use Dravencms\AdminModule\Components\Gallery\GalleryGrid\GalleryGrid;
use Dravencms\AdminModule\Components\Gallery\GalleryGrid\GalleryGridFactory;
use Dravencms\AdminModule\Components\Gallery\PictureForm\PictureForm;
use Dravencms\AdminModule\Components\Gallery\PictureForm\PictureFormFactory;
use Dravencms\AdminModule\Components\Gallery\PictureGrid\PictureGrid;
use Dravencms\AdminModule\Components\Gallery\PictureGrid\PictureGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Flash;
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
    public function renderDefault(): void
    {
        $this->template->h1 = 'Galleries';
    }

    /**
     * @isAllowed(gallery,edit)
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit(int $id = null): void
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
     * @param int $galleryId
     * @param int|null $pictureId
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEditPicture(int $galleryId, int $pictureId = null)
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
     * @param int $galleryId
     */
    public function actionNewPictureFromDirectory(int $galleryId): void
    {
        $this->gallery = $this->galleryRepository->getOneById($galleryId);
        $this->template->h1 = 'New pictures from directory into gallery: '. $this->gallery->getIdentifier();
    }

    /**
     * @param $id
     */
    public function actionPictures(int $id): void
    {
        $this->gallery = $this->galleryRepository->getOneById($id);
        $this->template->gallery = $this->gallery;
        $this->template->h1 = 'Photos';
    }

    /**
     * @return GalleryForm
     */
    public function createComponentFormGallery(): GalleryForm
    {
        $control = $this->galleryFormFactory->create($this->gallery);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Gallery has been successfully saved', Flash::SUCCESS);
            $this->redirect("Gallery:");
        };

        return $control;
    }

    /**
     * @return PictureForm
     */
    public function createComponentFormPicture(): PictureForm
    {
        $control = $this->pictureFormFactory->create($this->gallery, $this->picture);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Picture has been successfully saved', Flash::SUCCESS);
            $this->redirect('Gallery:pictures', $this->gallery->getId());
        };
        return $control;
    }

    /**
     * @return GalleryGrid
     */
    public function createComponentGridGallery(): GalleryGrid
    {
        $control = $this->galleryGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Gallery has been successfully deleted', Flash::SUCCESS);
            $this->redirect('Gallery:');
        };
        return $control;
    }

    /**
     * @return PictureGrid
     */
    public function createComponentGridPicture(): PictureGrid
    {
        $control = $this->pictureGridFactory->create($this->gallery);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Picture has been successfully deleted', Flash::SUCCESS);
            $this->redirect('Gallery:pictures', $this->gallery->getId());
        };
        return $control;
    }

    /**
     * @return DirectoryPictureForm
     */
    public function createComponentFormDirectoryPicture(): DirectoryPictureForm
    {
        $control = $this->directoryPictureFormFactory->create($this->gallery);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Pictures has been successfully saved', Flash::SUCCESS);
            $this->redirect('Gallery:pictures', $this->gallery->getId());
        };
        return $control;
    }
}
