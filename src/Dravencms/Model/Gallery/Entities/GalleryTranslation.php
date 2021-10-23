<?php declare(strict_types = 1);
namespace Dravencms\Model\Gallery\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette;

/**
 * Class GalleryTranslation
 * @package App\Model\Gallery\Entities
 * @ORM\Entity
 * @ORM\Table(name="galleryGalleryTranslation")
 */
class GalleryTranslation
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=255, unique=true,nullable=false)
     */
    private $slug;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=false)
     */
    private $description;

    /**
     * @var Gallery
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="translations")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    private $gallery;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * GalleryTranslation constructor.
     * @param Gallery $gallery
     * @param Locale $locale
     * @param string $name
     * @param string $description
     */
    public function __construct(Gallery $gallery, Locale $locale, string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
        $this->gallery = $gallery;
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param Gallery $gallery
     */
    public function setGallery(Gallery $gallery): void
    {
        $this->gallery = $gallery;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Gallery
     */
    public function getGallery(): Gallery
    {
        return $this->gallery;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
}

