<?php declare(strict_types = 1);
namespace Dravencms\Model\Gallery\Entities;

use Dravencms\Model\File\Entities\StructureFile;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Class PictureTranslation
 * @package App\Model\Gallery\Entities
 * @ORM\Entity
 * @ORM\Table(name="galleryPictureTranslation", uniqueConstraints={@UniqueConstraint(name="picture_translation_name_unique", columns={"picture_id", "locale_id", "name"})})
 */
class PictureTranslation
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
     * @ORM\Column(type="text",nullable=true)
     */
    private $description;

    /**
     * @var Picture
     * @ORM\ManyToOne(targetEntity="Picture", inversedBy="translations")
     * @ORM\JoinColumn(name="picture_id", referencedColumnName="id")
     */
    private $picture;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * PictureTranslation constructor.
     * @param Picture $picture
     * @param Locale $locale
     * @param string $name
     * @param string|null $description
     */
    public function __construct(Picture $picture, Locale $locale, string $name, string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->picture = $picture;
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
     * @param string|null $description
     */
    public function setDescription(string $description = null): void
    {
        $this->description = $description;
    }

    /**
     * @param Picture $picture
     */
    public function setPicture(Picture $picture): void
    {
        $this->picture = $picture;
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
     * @return Picture
     */
    public function getPicture(): Picture
    {
        return $this->picture;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }


}

