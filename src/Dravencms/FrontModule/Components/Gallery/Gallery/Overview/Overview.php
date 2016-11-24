<?php

namespace Dravencms\FrontModule\Components\Gallery\Gallery\Overview;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
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

    /**
     * Overview constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param GalleryRepository $galleryRepository
     */
    public function __construct(ICmsActionOption $cmsActionOption, GalleryRepository $galleryRepository)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->galleryRepository = $galleryRepository;
    }


    public function render()
    {
        $template = $this->template;
        $galleries = $this->galleryRepository->getByInOverview();

        $template->galleries = $galleries;

        $template->setFile(__DIR__ . '/overview.latte');
        $template->render();
    }
}
