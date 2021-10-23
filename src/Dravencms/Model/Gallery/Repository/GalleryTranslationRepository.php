<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\GalleryTranslation;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\ILocale;

class GalleryTranslationRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|GalleryTranslation */
    private $galleryTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->galleryTranslationRepository = $entityManager->getRepository(GalleryTranslation::class);
    }

    /**
     * @param string $name
     * @param ILocale $locale
     * @param Gallery|null $galleryIgnore
     * @return bool
     */
    public function isNameFree(string $name, ILocale $locale, Gallery $galleryIgnore = null): bool
    {
        $qb = $this->galleryTranslationRepository->createQueryBuilder('gt')
            ->select('gt')
            ->join('gt.gallery', 'g')
            ->where('gt.name = :name')
            ->andWhere('gt.locale = :locale')
            ->setParameters([
                'name' => $name,
                'locale' => $locale
            ]);

        if ($galleryIgnore)
        {
            $qb->andWhere('g != :galleryIgnore')
                ->setParameter('galleryIgnore', $galleryIgnore);
        }

        $query = $qb->getQuery();
        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param Gallery $gallery
     * @param ILocale $locale
     * @return GalleryTranslation|null
     */
    public function getTranslation(Gallery $gallery, ILocale $locale): ?GalleryTranslation
    {
        $qb = $this->galleryTranslationRepository->createQueryBuilder('gt')
            ->select('gt')
            ->where('gt.locale = :locale')
            ->andWhere('gt.gallery = :gallery')
            ->setParameters([
                'gallery' => $gallery,
                'locale' => $locale
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }
}