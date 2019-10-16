<?php

namespace App\Entity\NewsItem;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * NewsItem objects represent news items published on the site.
 *
 * News items each have a category, a start date, and an end date. After the end date has passed,
 * they will no longer be shown on the main news page, but moved to a section of archived news. News
 * item categories are defined in the `config\services.yaml` file.
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"category", "title"},
 *     errorPath="title",
 *     message="There is already a news item with this title in this category."
 * )
 */
class NewsItem
{
    /**
     * The news item's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The news item's category.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    /**
     * The news item's title.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * The news item's date
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * The date at which the news item expires.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * The news item's content.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * Constructor function.
     *
     * @param string|null The news item's category.
     * @return void
     */
    public function __construct(?string $category = null)
    {
        $this->id = null; // Doctrine takes care of this
        $this->category = $category;
        $this->title = null;
        $this->date = new \DateTime();
        $this->end = new \DateTime();
        $this->end->add(new \DateInterval('P4M'));
        $this->content = null;
    }

    // ToString function
    public function __toString(): string
    {
        return $this->title;
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

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
