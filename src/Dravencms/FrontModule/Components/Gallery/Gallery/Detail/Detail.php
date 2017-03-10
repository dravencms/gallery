<?php

namespace Dravencms\FrontModule\Components\Gallery\Gallery\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Dravencms\Model\Gallery\Repository\GalleryTranslationRepository;
use Dravencms\Model\Gallery\Repository\PictureTranslationRepository;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponent;

/**
 * Homepage presenter.
 */
class Detail extends BaseControl
{
    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var GalleryRepository */
    private $galleryRepository;

    /** @var GalleryTranslationRepository */
    private $galleryTranslationRepository;

    /** @var PictureTranslationRepository */
    private $pictureTranslationRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param GalleryRepository $galleryRepository
     * @param GalleryTranslationRepository $galleryTranslationRepository
     * @param PictureTranslationRepository $pictureTranslationRepository
     * @param CurrentLocale $currentLocale
     */
    public function __construct(
        ICmsActionOption $cmsActionOption,
        GalleryRepository $galleryRepository,
        GalleryTranslationRepository $galleryTranslationRepository,
        PictureTranslationRepository $pictureTranslationRepository,
        CurrentLocale $currentLocale
    )
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->galleryRepository = $galleryRepository;
        $this->galleryTranslationRepository = $galleryTranslationRepository;
        $this->pictureTranslationRepository = $pictureTranslationRepository;
        $this->currentLocale = $currentLocale;
    }

    public function render()
    {
        $template = $this->template;
        
        $gallery = $this->galleryRepository->getOneById($this->cmsActionOption->getParameter('id'));
        $template->galleryTranslation = $this->galleryTranslationRepository->getTranslation($gallery, $this->currentLocale);

        $pictureTranslations = [];
        foreach($gallery->getPictures() AS $picture)
        {
            $pictureTranslations[] = $this->pictureTranslationRepository->getTranslation($picture, $this->currentLocale);
        }

        $template->pictureTranslations = $pictureTranslations;

        $template->setFile($this->cmsActionOption->getTemplatePath(__DIR__.'/detail.latte'));
        $template->render();
    }
}
