<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Model\Gallery\Entities\Gallery;
use Kdyby\Doctrine\EntityManager;
use Nette;

class GalleryRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
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
     * @param $id
     * @return mixed|null|Gallery
     */
    public function getOneById($id)
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
    public function getByInOverview($isInOverview = true)
    {
        return $this->galleryRepository->findBy(['isInOverview' => $isInOverview]);
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
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
     * @param $identifier
     * @param Gallery|null $galleryIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree($identifier, Gallery $galleryIgnore = null)
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
    public function getOneByParameters(array $parameters)
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneBySlug($slug)
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