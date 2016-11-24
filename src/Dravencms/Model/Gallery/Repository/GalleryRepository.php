<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Repository;

use Dravencms\Locale\TLocalizedRepository;
use Dravencms\Model\Gallery\Entities\Gallery;
use Gedmo\Translatable\TranslatableListener;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

class GalleryRepository implements ICmsComponentRepository
{
    use TLocalizedRepository;

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
     * @param $name
     * @param ILocale $locale
     * @param Gallery|null $galleryIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Gallery $galleryIgnore = null)
    {
        $qb = $this->galleryRepository->createQueryBuilder('g')
            ->select('g')
            ->where('g.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($galleryIgnore)
        {
            $qb->andWhere('g != :galleryIgnore')
                ->setParameter('galleryIgnore', $galleryIgnore);
        }

        $query = $qb->getQuery();
        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());
        
        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @return Gallery[]
     */
    public function getAll()
    {
        return $this->galleryRepository->findAll();
    }

    /**
     * @param string $componentAction
     * @return CmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
            case 'Tag':
                $return = [];
                /** @var Gallery $carousel */
                foreach ($this->galleryRepository->findBy(['isActive' => true]) AS $carousel) {
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
        $found = $this->findTranslatedOneBy($this->galleryRepository, $locale, $parameters + ['isActive' => true]);

        if ($found)
        {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }

}