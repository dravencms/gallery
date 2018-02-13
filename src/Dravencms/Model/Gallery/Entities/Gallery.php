<?php
namespace Dravencms\Model\Gallery\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\ILocale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
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
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
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
     * @param $identifier
     * @param \DateTime $date
     * @param bool $isActive
     * @param bool $isShowName
     * @param bool $isInOverview
     */
    public function __construct(
        $identifier,
        \DateTime $date,
        $isActive = true,
        $isShowName = true,
        $isInOverview = true
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
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate(\DateTime $date = null)
    {
        $this->date = $date;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName($isShowName)
    {
        $this->isShowName = $isShowName;
    }

    /**
     * @param boolean $isInOverview
     */
    public function setIsInOverview($isInOverview)
    {
        $this->isInOverview = $isInOverview;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isShowName()
    {
        return $this->isShowName;
    }

    /**
     * @return boolean
     */
    public function isInOverview()
    {
        return $this->isInOverview;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return Picture[]|ArrayCollection
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @return ArrayCollection|GalleryTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getPrimaryPicture()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("isPrimary", true));
        return $this->getPictures()->matching($criteria);
    }

    /**
     * @param ILocale $locale
     * @return GalleryTranslation
     */
    public function translate(ILocale $locale)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("locale", $locale));
        return $this->getTranslations()->matching($criteria)->first();
    }
}

