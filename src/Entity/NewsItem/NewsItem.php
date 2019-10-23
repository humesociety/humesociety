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
     * @throws \Exception
     * @return void
     */
    public function __construct(?string $category = null)
    {
        // persisted properties
        $this->id = null; // Doctrine takes care of this
        $this->category = $category;
        $this->title = null;
        $this->date = new \DateTime('today');
        $this->end = (new \DateTime('today'))->add(new \DateInterval('P4M'));
        $this->content = null;
    }

    /**
     * ToString function
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->title ? $this->title : 'uninitialised news item';
    }

    /**
     * Get the news item's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the news item's category (null when the object is first created).
     *
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * Set the news item's category.
     *
     * @param string $category The news item's category.
     * @return self
     */
    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get the news item's title (null when the object is first created).
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the news item's title.
     *
     * @param string $title The news item's title.
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the news item's date.
     *
     * @return \DateTimeInterface
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Set the news item's date.
     *
     * @param \DateTimeInterface $date
     * @return self
     */
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get the date at which the news item expires.
     *
     * @return \DateTimeInterface
     */
    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    /**
     * Set the date at which the news items expires.
     *
     * @param \DateTimeInterface $end The date at which the news item expires.
     * @return self
     */
    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Get the news item's content (null when the object is first created).
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the news item's content.
     *
     * @param string $content The new item's content.
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
