<?php
namespace Dravencms\Model\Gallery\Entities;

use App\Model\File\Entities\StructureFile;
use App\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
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
class Gallery extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $name;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="text",nullable=false)
     */
    private $description;

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
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    /**
     * @var ArrayCollection|Picture[]
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="gallery",cascade={"persist"})
     */
    private $pictures;


    /**
     * Gallery constructor.
     * @param string $name
     * @param string $description
     * @param bool $isActive
     * @param bool $isShowName
     * @param bool $isInOverview
     */
    public function __construct($name, $description, $isActive = true, $isShowName = true, $isInOverview = true)
    {
        $this->name = $name;
        $this->description = $description;
        $this->isActive = $isActive;
        $this->isShowName = $isShowName;
        $this->isInOverview = $isInOverview;

        $this->pictures = new ArrayCollection();
    }


    /**
     * @param $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Picture[]|ArrayCollection
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    public function getPrimaryPicture()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("isPrimary", true));
        return $this->getPictures()->matching($criteria);
    }
}

