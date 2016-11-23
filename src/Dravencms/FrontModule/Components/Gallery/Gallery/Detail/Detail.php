<?php

namespace Dravencms\FrontModule\Components\Gallery\Gallery;

use Dravencms\Components\BaseControl;
use App\Model\Gallery\Repository\GalleryRepository;
use Salamek\Cms\ICmsActionOption;

/**
 * Homepage presenter.
 */
class Detail extends BaseControl
{
    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var GalleryRepository */
    private $galleryRepository;

    public function __construct(ICmsActionOption $cmsActionOption, GalleryRepository $galleryRepository)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->galleryRepository = $galleryRepository;
    }

    public function render()
    {
        $template = $this->template;
        
        $gallery = $this->galleryRepository->getOneById($this->cmsActionOption->getParameter('id'));
        $template->gallery = $gallery;

        $template->setFile(__DIR__ . '/detail.latte');
        $template->render();
    }
}
