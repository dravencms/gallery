<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Model\Gallery\Entities\Gallery;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsComponentRepository;

class GalleryCmsRepository implements ICmsComponentRepository
{
    /** @var GalleryRepository */
    private $galleryRepository;

    /**
     * GalleryCmsRepository constructor.
     * @param GalleryRepository $galleryRepository
     */
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
                    $return[] = new CmsActionOption($carousel->getIdentifier(), ['id' => $carousel->getId()]);
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
     * @return null|CmsActionOption
     */
    public function getActionOption(string $componentAction, array $parameters): ?CmsActionOption
    {
        /** @var Gallery $found */
        $found = $this->galleryRepository->getOneByParameters($parameters + ['isActive' => true]);

        if ($found) {
            return new CmsActionOption($found->getIdentifier(), $parameters);
        }

        return null;
    }

}