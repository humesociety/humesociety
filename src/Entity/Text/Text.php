<?php

namespace App\Entity\Text;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Text objects are small bits of customizable text used in the web site.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Text\TextRepository")
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
     * ToString function.
     */
    public function __toString(): string
    {
        return $this->content;
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
}
