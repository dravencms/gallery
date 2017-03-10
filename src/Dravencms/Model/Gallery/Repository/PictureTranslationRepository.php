<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;


use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Model\Gallery\Entities\PictureTranslation;
use Dravencms\Model\Locale\Entities\ILocale;
use Kdyby\Doctrine\EntityManager;
use Nette;

class PictureTranslationRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
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
     * @param $name
     * @param ILocale $locale,
     * @param Gallery $gallery
     * @param Picture|null $pictureIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Gallery $gallery, Picture $pictureIgnore = null)
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
     * @return PictureTranslation
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTranslation(Picture $picture, ILocale $locale)
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