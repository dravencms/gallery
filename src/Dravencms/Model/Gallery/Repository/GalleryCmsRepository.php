<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Model\Gallery\Entities\Gallery;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

class GalleryCmsRepository implements ICmsComponentRepository
{
    /** @var GalleryRepository */
    private $galleryRepository;

    public function __construct(GalleryRepository $galleryRepository)
    {
        $this->galleryRepository = $galleryRepository;
    }

    /**
     * @param string $componentAction
     * @return CmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction) {
            case 'Detail':
            case 'Tag':
                $return = [];
                /** @var Gallery $carousel */
                foreach ($this->galleryRepository->getActive() AS $carousel) {
                    $return[] = new CmsActionOption($carousel->getName(), ['id' => $carousel->getId()]);
                }
                break;

            case 'Overview':
                return null;
                break;

            default:
                return false;
                break;
        }


        return $return;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        /** @var Gallery $found */
        $found = $this->galleryRepository->findTranslatedOneBy($this->galleryRepository, $locale, $parameters + ['isActive' => true]);

        if ($found) {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }

}