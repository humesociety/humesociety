<?php

namespace App\Entity\Text;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Text objects are small bits of variable text used in the web site, whose content is stored in the database.
 *
 * Text variables need to be "declared" in the `services.yml` file, which defines a human-readable title
 * and description alongside the label.
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields="label",
 *     message="There is already a text variable with this label in the database."
 * )
 */
class Text
{
    /**
     * The text's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The text's (unique) label.
     *
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $label;

    /**
     * The text itself.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * The text's title. This is not stored in the database, but in the `services.yml` file.
     *
     * @var string
     */
    private $title;

    /**
     * The text's description. This is not stored in the database, but in the `services.yml` file.
     *
     * @var string
     */
    private $description;

    /**
     * Constructor function.
     *
     * @var string The text's label.
     * @return void
     */
    public function __construct(string $label)
    {
        $this->id = null; // Doctrine takes care of this
        $this->label = $label;
        $this->content = null;
        $this->title = null;
        $this->description = null;
    }

    /**
     * ToString function.
     */
    public function __toString(): string
    {
        return $this->title ? $this->title : 'uninitialised text object';
    }

    /**
     * Get the text's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the text's label (null when the object is first created).
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set the text's label.
     *
     * @param string The text's label.
     * @return self
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get the text's content (null when the object is first created).
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the text's content.
     *
     * @param string The text's content.
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the text's title (null when the object is first created).
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the text's title.
     *
     * @param string The text's title.
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the text's description (null when the object is first created).
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the text's description.
     *
     * @param string The text's description.
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
