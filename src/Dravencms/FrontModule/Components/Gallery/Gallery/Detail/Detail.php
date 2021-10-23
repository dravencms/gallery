<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Gallery\Gallery\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Dravencms\Model\Gallery\Repository\GalleryTranslationRepository;
use Dravencms\Model\Gallery\Repository\PictureTranslationRepository;
use Dravencms\Model\Locale\Entities\Locale;
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

    /** @var Locale */
    private $currentLocale;

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param GalleryRepository $galleryRepository
     * @param GalleryTranslationRepository $galleryTranslationRepository
     * @param PictureTranslationRepository $pictureTranslationRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @throws \Exception
     */
    public function __construct(
        ICmsActionOption $cmsActionOption,
        GalleryRepository $galleryRepository,
        GalleryTranslationRepository $galleryTranslationRepository,
        PictureTranslationRepository $pictureTranslationRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        $this->cmsActionOption = $cmsActionOption;
        $this->galleryRepository = $galleryRepository;
        $this->galleryTranslationRepository = $galleryTranslationRepository;
        $this->pictureTranslationRepository = $pictureTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }

    public function render(): void
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
