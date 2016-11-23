<?php
namespace Dravencms\Model\Gallery\Entities;

use App\Model\File\Entities\StructureFile;
use App\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Picture
 * @package App\Model\Gallery\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="galleryPicture")
 */
class Picture extends Nette\Object
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
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isPrimary;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    /**
     * @var StructureFile
     * @ORM\ManyToOne(targetEntity="\App\Model\File\Entities\StructureFile", inversedBy="articles")
     * @ORM\JoinColumn(name="structure_file_id", referencedColumnName="id")
     */
    private $structureFile;

    /**
     * @var Gallery
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="pictures")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    private $gallery;

    /**
     * @var \Doctrine\Common\Collections\Collection|Tag[]
     *
     * @ORM\ManyToMany(targetEntity="\App\Model\Tag\Entities\Tag", inversedBy="galleryPictures")
     * @ORM\JoinTable(
     *  name="picture_tag",
     *  joinColumns={
     *      @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     *  }
     * )
     */
    private $tags;

    /**
     * Picture constructor.
     * @param Gallery $gallery
     * @param StructureFile $structureFile
     * @param $name
     * @param $description
     * @param bool $isActive
     * @param bool $isPrimary
     */
    public function __construct(Gallery $gallery, StructureFile $structureFile, $name, $description, $isActive = true, $isPrimary = false)
    {
        $this->gallery = $gallery;
        $this->name = $name;
        $this->description = $description;
        $this->isActive = $isActive;
        $this->isPrimary = $isPrimary;
        $this->structureFile = $structureFile;

        $this->tags = new ArrayCollection();
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
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param StructureFile $structureFile
     */
    public function setStructureFile(StructureFile $structureFile)
    {
        $this->structureFile = $structureFile;
    }

    /**
     * @param boolean $isPrimary
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        if ($this->tags->contains($tag))
        {
            return;
        }
        $this->tags->add($tag);
        $tag->addGalleryPicture($this);
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        if (!$this->tags->contains($tag))
        {
            return;
        }
        $this->tags->removeElement($tag);
        $tag->removeGalleryPicture($this);
    }

    /**
     *
     * @param ArrayCollection $tags
     */
    public function setTags(ArrayCollection $tags)
    {
        //Remove all not in
        foreach($this->tags AS $tag)
        {
            if (!$tags->contains($tag))
            {
                $this->addTag($tag);
            }
        }

        //Add all new
        foreach($tags AS $tag)
        {
            if (!$this->tags->contains($tag))
            {
                $this->removeTag($tag);
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
    public function isPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return StructureFile
     */
    public function getStructureFile()
    {
        return $this->structureFile;
    }

    /**
     * @return \App\Model\Tag\Entities\Tag[]|\Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }
}

