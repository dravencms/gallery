<?php

namespace Dravencms\FrontModule\Components\Gallery\Gallery;

use Dravencms\Components\BaseControl;
use Salamek\Cms\ICmsActionOption;

/**
 * Homepage presenter.
 */
class Tag extends BaseControl
{
    /** @var ICmsActionOption */
    public $cmsActionOption;

    public function __construct(ICmsActionOption $cmsActionOption)
    {
        $this->cmsActionOption = $cmsActionOption;
    }

    public function handleTag($tagId)
    {
        $this->tagId = $tagId;
    }

    public function render()
    {
        $this->template->fotos = $this->galleryModel->where('id', $id)
            ->where('active', 1)
            ->fetch()
            ->related('galleryPhoto');

        $this->template->fotosTags = array();
        foreach ($this->template->fotos AS $fotos) {
            //Vypsat vsechny stitky
            foreach ($fotos->related('galleryPhotoTagsLink')->group('galleryPhotoTagsId') AS $fotoTagsLinks) {
                $this->template->fotosTags[] = $fotoTagsLinks;
            }
        }
    }

}
