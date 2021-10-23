<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Model\Gallery\Entities\Picture;
use Dravencms\Database\EntityManager;


class PictureRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|Picture */
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
     * @param int $id
     * @return Picture|null
     */
    public function getOneById(int $id): ?Picture
    {
        return $this->pictureRepository->find($id);
    }

    /**
     * @param $id
     * @return Picture[]
     */
    public function getById($id)
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
     * @return mixed
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
     * @param string $identifier
     * @param Gallery $gallery
     * @param Picture|null $pictureIgnore
     * @return bool
     */
    public function isIdentifierFree(string $identifier, Gallery $gallery, Picture $pictureIgnore = null): bool
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