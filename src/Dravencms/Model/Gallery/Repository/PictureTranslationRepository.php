<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;


use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Entities\PictureTranslation;
use Dravencms\Model\Locale\Entities\ILocale;
use Dravencms\Database\EntityManager;

class PictureTranslationRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|PictureTranslation */
    private $pictureTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->pictureTranslationRepository = $entityManager->getRepository(PictureTranslation::class);
    }

    /**
     * @param string $name
     * @param ILocale $locale
     * @param Gallery $gallery
     * @param Picture|null $pictureIgnore
     * @return bool
     */
    public function isNameFree(string $name, ILocale $locale, Gallery $gallery, Picture $pictureIgnore = null): bool
    {
        $qb = $this->pictureTranslationRepository->createQueryBuilder('pt')
            ->select('pt')
            ->join('pt.picture', 'p')
            ->where('pt.name = :name')
            ->andWhere('p.gallery = :gallery')
            ->andWhere('pt.locale = :locale')
            ->setParameters([
                'name' => $name,
                'gallery' => $gallery,
                'locale' => $locale
            ]);

        if ($pictureIgnore)
        {
            $qb->andWhere('p != :pictureIgnore')
                ->setParameter('pictureIgnore', $pictureIgnore);
        }

        $query = $qb->getQuery();

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param Picture $picture
     * @param ILocale $locale
     * @return PictureTranslation|null
     */
    public function getTranslation(Picture $picture, ILocale $locale): ?PictureTranslation
    {
        $qb = $this->pictureTranslationRepository->createQueryBuilder('pt')
            ->select('pt')
            ->where('pt.locale = :locale')
            ->andWhere('pt.picture = :picture')
            ->setParameters([
                'picture' => $picture,
                'locale' => $locale
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }
}