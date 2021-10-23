<?php declare(strict_types = 1);
namespace Dravencms\Model\Gallery\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\ILocale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class Gallery
 * @package App\Model\Gallery\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="galleryGallery")
 */
class Gallery
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false, unique=true)
     */
    private $identifier;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isShowName;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isInOverview;

    /**
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var ArrayCollection|Picture[]
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="gallery",cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $pictures;

    /**
     * @var ArrayCollection|GalleryTranslation[]
     * @ORM\OneToMany(targetEntity="GalleryTranslation", mappedBy="gallery",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * Gallery constructor.
     * @param string $identifier
     * @param \DateTime|null $date
     * @param bool $isActive
     * @param bool $isShowName
     * @param bool $isInOverview
     */
    public function __construct(
        string $identifier,
        \DateTime $date = null,
        bool $isActive = true,
        bool $isShowName = true,
        bool $isInOverview = true
    )
    {
        $this->identifier = $identifier;
        $this->date = $date;
        $this->isActive = $isActive;
        $this->isShowName = $isShowName;
        $this->isInOverview = $isInOverview;

        $this->pictures = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate(\DateTime $date = null): void
    {
        $this->date = $date;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName(bool $isShowName): void
    {
        $this->isShowName = $isShowName;
    }

    /**
     * @param boolean $isInOverview
     */
    public function setIsInOverview(bool $isInOverview): void
    {
        $this->isInOverview = $isInOverview;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isShowName(): bool
    {
        return $this->isShowName;
    }

    /**
     * @return boolean
     */
    public function isInOverview(): bool
    {
        return $this->isInOverview;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return Picture[]|ArrayCollection
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    /**
     * @return ArrayCollection|GalleryTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
    
    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getPrimaryPicture(): Collection
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("isPrimary", true));
        return $this->getPictures()->matching($criteria);
    }

    /**
     * @param ILocale $locale
     * @return GalleryTranslation
     */
    public function translate(ILocale $locale): GalleryTranslation
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("locale", $locale));
        return $this->getTranslations()->matching($criteria)->first();
    }
}

