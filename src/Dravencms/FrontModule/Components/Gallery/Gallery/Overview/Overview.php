<?php

namespace Dravencms\FrontModule\Components\Gallery\Gallery\Overview;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Dravencms\Model\Gallery\Repository\GalleryTranslationRepository;
use Salamek\Cms\ICmsActionOption;

/**
 * Homepage presenter.
 */
class Overview extends BaseControl
{
    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var GalleryRepository */
    private $galleryRepository;

    /** @var GalleryTranslationRepository */
    private $galleryTranslationRepository;

    /** @var ILocale */
    private $currentLocale;

    /**
     * Overview constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param GalleryRepository $galleryRepository
     * @param GalleryTranslationRepository $galleryTranslationRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function __construct(
        ICmsActionOption $cmsActionOption,
        GalleryRepository $galleryRepository,
        GalleryTranslationRepository $galleryTranslationRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->galleryRepository = $galleryRepository;
        $this->galleryTranslationRepository = $galleryTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }


    public function render()
    {
        $template = $this->template;
        $galleries = $this->galleryRepository->getByInOverview();

        $galleryTranslations = [];
        foreach ($galleries AS $gallery)
        {
            $galleryTranslations[] = $this->galleryTranslationRepository->getTranslation($gallery, $this->currentLocale);
        }

        $template->galleryTranslations = $galleryTranslations;

        $template->setFile($this->cmsActionOption->getTemplatePath(__DIR__.'/overview.latte'));
        $template->render();
    }
}
