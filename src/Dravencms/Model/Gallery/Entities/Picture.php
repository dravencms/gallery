<?php declare(strict_types = 1);
namespace Dravencms\Model\Gallery\Entities;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Dravencms\Model\File\Entities\StructureFile;
use Dravencms\Model\File\Entities\StructureFileLink;
use Dravencms\Model\Locale\Entities\ILocale;
use Dravencms\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class Picture
 * @package App\Model\Gallery\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="galleryPicture")
 */
class Picture
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
     * @var StructureFile
     * @ORM\ManyToOne(targetEntity="\Dravencms\Model\File\Entities\StructureFile")
     * @ORM\JoinColumn(name="structure_file_id", referencedColumnName="id")
     */
    private $structureFile;

    /**
     * @var StructureFileLink
     * @ORM\ManyToOne(targetEntity="\Dravencms\Model\File\Entities\StructureFileLink")
     * @ORM\JoinColumn(name="structure_file_link_id", referencedColumnName="id")
     */
    private $structureFileLink;

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
     * @ORM\ManyToMany(targetEntity="\Dravencms\Model\Tag\Entities\Tag")
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
     * @var ArrayCollection|PictureTranslation[]
     * @ORM\OneToMany(targetEntity="PictureTranslation", mappedBy="picture",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * Picture constructor.
     * @param Gallery $gallery
     * @param StructureFileLink $structureFileLink
     * @param $identifier
     * @param bool $isActive
     * @param bool $isPrimary
     */
    public function __construct(Gallery $gallery, StructureFileLink $structureFileLink, string $identifier, bool $isActive = true, bool $isPrimary = false)
    {
        $this->identifier = $identifier;
        $this->gallery = $gallery;
        $this->isActive = $isActive;
        $this->isPrimary = $isPrimary;
        $this->structureFileLink = $structureFileLink;

        $this->tags = new ArrayCollection();
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
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @param StructureFile $structureFile
     */
    public function setStructureFile(StructureFile $structureFile = null): void
    {
        $this->structureFile = $structureFile;
    }

    /**
     * @param StructureFileLink $structureFileLink
     */
    public function setStructureFileLink(StructureFileLink $structureFileLink): void
    {
        $this->structureFileLink = $structureFileLink;
    }

    /**
     * @param boolean $isPrimary
     */
    public function setIsPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag): void
    {
        if ($this->tags->contains($tag))
        {
            return;
        }
        $this->tags->add($tag);
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag))
        {
            return;
        }
        $this->tags->removeElement($tag);
    }

    /**
     *
     * @param ArrayCollection $tags
     */
    public function setTags(ArrayCollection $tags): void
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
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return StructureFile
     * @deprecated Use getStructureFileLink()->getStructureFile()
     */
    public function getStructureFile(): StructureFile
    {
        return ($this->structureFileLink ? $this->structureFileLink->getStructureFile() : $this->structureFile);
    }

    /**
     * @return StructureFileLink
     */
    public function getStructureFileLink(): StructureFileLink
    {
        return $this->structureFileLink;
    }

    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection|\Dravencms\Model\Tag\Entities\Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @return Gallery
     */
    public function getGallery(): Gallery
    {
        return $this->gallery;
    }

    /**
     * @return ArrayCollection|PictureTranslation[]
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
     * @param ILocale $locale
     * @return PictureTranslation
     */
    public function translate(ILocale $locale): PictureTranslation
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("locale", $locale));
        return $this->getTranslations()->matching($criteria)->first();
    }
}

