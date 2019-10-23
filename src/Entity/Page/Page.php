<?php

namespace App\Entity\Page;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Page objects represent pages on the public side of the web site.
 *
 * Page must belong to a section, and each section must have an index page. Pages have one of several
 * templates, and possibly (depending on the template) some custom content. Sections and templates
 * are defined in the `config/services.yaml` file, and each template has its own Twig file in the
 * `templates\site\templates` directory.
 *
 * @ORM\Entity()
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
 */
class Page
{
    /**
     * The page's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The page's section.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $section;

    /**
     * The page's position within its section.
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * The page's slug.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * The page's title
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * The page's template.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $template;

    /**
     * The page's content.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * Constructor function
     *
     * @return void
     */
    public function __construct()
    {
        // persisted properties
        $this->id = null; // Doctrine takes care of this
        $this->section = null;
        $this->position = null;
        $this->slug = null;
        $this->title = null;
        $this->template = null;
        $this->content = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return ($this->section && $this->slug) ? $this->section.'/'.$this->slug : 'uninitialised page';
    }

    /**
     * Get the page's unique identifier in the database (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the page's section (null when the object is first created).
     *
     * @return string|null
     */
    public function getSection(): ?string
    {
        return $this->section;
    }

    /**
     * Set the page's section.
     *
     * @var string $section The page's section.
     * @return self
     */
    public function setSection(string $section): self
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Get the page's position (null when the object is first created).
     *
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * Set the page's position.
     *
     * @var int $position The page's position.
     * @return self
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get the page's slug (null when the object is first created).
     *
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Set the page's slug.
     *
     * @var string $slug The page's slug.
     * @return self
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get the page's title (null when the object is first created).
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the page's title.
     *
     * @var string $title The page's title.
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the page's template (null when the object is first created).
     *
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Set the page's template.
     *
     * @var string $template The page's template.
     * @return self
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get the page's content (null when the object is first created).
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the page's content.
     *
     * @var string $content The page's content.
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
}
