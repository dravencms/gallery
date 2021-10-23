<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Model\Gallery\Entities\Gallery;
use Dravencms\Database\EntityManager;


class GalleryRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|Gallery */
    private $galleryRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->galleryRepository = $entityManager->getRepository(Gallery::class);
    }

    /**
     * @param int $id
     * @return Gallery|null
     */
    public function getOneById(int $id): ?Gallery
    {
        return $this->galleryRepository->find($id);
    }

    /**
     * @param $id
     * @return Gallery[]
     */
    public function getById($id)
    {
        return $this->galleryRepository->findBy(['id' => $id]);
    }

    /**
     * @param bool $isInOverview
     * @return Gallery[]
     */
    public function getByInOverview(bool $isInOverview = true)
    {
        return $this->galleryRepository->findBy(['isInOverview' => $isInOverview]);
    }

    /**
     * @return mixed
     */
    public function getGalleryQueryBuilder()
    {
        $qb = $this->galleryRepository->createQueryBuilder('g')
            ->select('g');
        return $qb;
    }

    /**
     * @return Gallery[]
     */
    public function getActive()
    {
        return $this->galleryRepository->findBy(['isActive' => true]);
    }

    /**
     * @param string $identifier
     * @param Gallery|null $galleryIgnore
     * @return bool
     */
    public function isIdentifierFree(string $identifier, Gallery $galleryIgnore = null): bool
    {
        $qb = $this->galleryRepository->createQueryBuilder('g')
            ->select('g')
            ->where('g.identifier = :identifier')
            ->setParameters([
                'identifier' => $identifier
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
     * @param array $parameters
     * @return Gallery|null
     */
    public function getOneByParameters(array $parameters): ?Gallery
    {
        return $this->galleryRepository->findOneBy($parameters);
    }

    /**
     * @return Gallery[]
     */
    public function getAll()
    {
        return $this->galleryRepository->findAll();
    }

    /**
     * @return Gallery[]
     */
    public function getActiveByDate()
    {
        return $this->galleryRepository->findBy(['isActive' => true], ['date' => 'DESC']);
    }

    /**
     * @param int $offset
     * @return Gallery[]
     */
    public function getOverOffset(int $offset)
    {
        return $this->galleryRepository->findBy([], ['createdAt' => 'DESC'], null, $offset);
    }

    /**
     * @param $slug
     * @return Gallery|null
     */
    public function getOneBySlug($slug): ?Gallery
    {
        return $this->galleryRepository->createQueryBuilder('g')
            ->select('g')
            ->join('g.translations', 'gt')
            ->where('gt.slug = :slug')
            ->setParameters([
                'slug' => $slug
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}