<?php

namespace App\Entity\Page;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Page\PageRepository")
 * @UniqueEntity(
 *     fields={"section", "position"},
 *     errorPath="position",
 *     message="This section already has a page at this position."
 * )
 * @UniqueEntity(
 *     fields={"section", "slug"},
 *     errorPath="slug",
 *     message="This section already has a page with this slug."
 * )
 * @UniqueEntity(
 *     fields={"section", "title"},
 *     errorPath="title",
 *     message="This section already has a page with this title."
 * )
 *
 * Page objects represent pages on the public side of the web site. They must each belong to a
 * section, and each section must have an index page. They have one of several templates, and
 * possibly (depending on the template) some custom content.
 *
 * Sections and templates are defined in the `config/services.yaml` file, and each template has its
 * own Twig file in the `templates\site\templates` directory.
 */
class Page
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $section;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $template;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    // ToString function
    public function __toString(): string
    {
        return $this->section.'/'.$this->slug;
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
