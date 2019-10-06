<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Kdyby\Doctrine\EntityManager;
use Nette;

class PictureRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $pictureRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->pictureRepository = $entityManager->getRepository(Picture::class);
    }

    /**
     * @param $id
     * @return mixed|null|Picture
     */
    public function getOneById(int $id)
    {
        return $this->pictureRepository->find($id);
    }

    /**
     * @param $id
     * @return Picture[]
     */
    public function getById(int $id)
    {
        return $this->pictureRepository->findBy(['id' => $id]);
    }

    /**
     * @return Picture[]
     */
    public function getAll()
    {
        return $this->pictureRepository->findAll();
    }

    /**
     * @param Gallery $gallery
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getPictureQueryBuilder(Gallery $gallery)
    {
        $qb = $this->pictureRepository->createQueryBuilder('p')
            ->select('p')
            ->where('p.gallery = :gallery')
            ->setParameter('gallery', $gallery);
        return $qb;
    }

    /**
     * @param $identifier
     * @param Gallery $gallery
     * @param Picture|null $pictureIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree(string $identifier, Gallery $gallery, Picture $pictureIgnore = null)
    {
        $qb = $this->pictureRepository->createQueryBuilder('p')
            ->select('p')
            ->where('p.identifier = :identifier')
            ->andWhere('p.gallery = :gallery')
            ->setParameters([
                'identifier' => $identifier,
                'gallery' => $gallery
            ]);

        if ($pictureIgnore)
        {
            $qb->andWhere('p != :pictureIgnore')
                ->setParameter('pictureIgnore', $pictureIgnore);
        }

        $query = $qb->getQuery();

        return (is_null($query->getOneOrNullResult()));
    }
    
}