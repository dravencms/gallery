<?php
namespace Dravencms\Model\Gallery\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette;

/**
 * Class GalleryTranslation
 * @package App\Model\Gallery\Entities
 * @ORM\Entity
 * @ORM\Table(name="galleryGalleryTranslation", uniqueConstraints={@UniqueConstraint(name="gallery_translation_name_unique", columns={"gallery_id", "locale_id", "name"})})
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
     * @param $name
     * @param $description
     */
    public function __construct(Gallery $gallery, Locale $locale, $name, $description)
    {
        $this->name = $name;
        $this->description = $description;
        $this->gallery = $gallery;
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
     * @param Gallery $gallery
     */
    public function setGallery(Gallery $gallery)
    {
        $this->gallery = $gallery;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
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
     * @return Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

