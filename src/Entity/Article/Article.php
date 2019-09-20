<?php

namespace App\Entity\Article;

use App\Entity\Issue\Issue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An article (or book review) in an issue of Hume Studies.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Article\ArticleRepository")
 * @UniqueEntity(
 *     fields={"issue", "position"},
 *     errorPath="position",
 *     message="There is already an article at this position in this issue."
 * )
 * @UniqueEntity(
 *     fields="museId",
 *     message="There is already an article with this Muse ID in the database."
 * )
 * @UniqueEntity(
 *     fields="doi",
 *     message="There is already an article with this DOI in the database."
 * )
 */
class Article
{
    /**
     * The article's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Get the article's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The issue the article is published in.
     *
     * @var Issue
     * @ORM\ManyToOne(targetEntity="App\Entity\Issue\Issue", inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $issue;

    /**
     * Get the issue the article is published in (null when the object is first created).
     *
     * @return Issue|null
     */
    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    /**
     * Set the issue the article is published in.
     *
     * @param Issue The issue the article is published in.
     */
    public function setIssue(Issue $issue): self
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * The position of the article in the issue.
     *
     * @var int
     * @ORM\Column(type="integer")
     * @Groups("json")
     */
    private $position;

    /**
     * Get the position of the article in the issue (null when the object is first created).
     *
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * Set the position of the article in the issue.
     *
     * @param int The position of the article in the issue.
     * @return self
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * The title of the article.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $title;

    /**
     * Get the title of the article (null when the object is first created).
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title of the article.
     *
     * @param string The title of the article.
     * @return self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * The authors of the article.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $authors;

    /**
     * Get the authors of the article.
     *
     * @return string|null
     */
    public function getAuthors(): ?string
    {
        return $this->authors;
    }

    /**
     * Set the authors of the article.
     *
     * @param string|null The authors of the article.
     * @return self
     */
    public function setAuthors(?string $authors)
    {
        $this->authors = $authors;
    }

    /**
     * The start page of the article.
     *
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("json")
     */
    private $startPage;

    /**
     * Get the start page of the article.
     *
     * @return int|null The start page of the article.
     */
    public function getStartPage(): ?int
    {
        return $this->startPage;
    }

    /**
     * Set the start page of the article.
     *
     * @param int|null The start page of the article.
     * @return self
     */
    public function setStartPage(?int $startPage)
    {
        $this->startPage = $startPage;
    }

    /**
     * The end page of the article.
     *
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("json")
     */
    private $endPage;

    /**
     * Get the end page of the article.
     *
     * @return int|null The start page of the article.
     */
    public function getEndPage(): ?int
    {
        return $this->endPage;
    }

    /**
     * Set the end page of the article.
     *
     * @param int|null The end page of the article.
     * @return self
     */
    public function setEndPage(?int $endPage)
    {
        $this->endPage = $endPage;
    }

    /**
     * @ORM\Column(type="integer", unique=true, nullable=true)
     * @Groups("json")
     */
    private $museId;

    public function getMuseId(): ?int
    {
        return $this->museId;
    }

    public function setMuseId(?int $museId): self
    {
        $this->museId = $museId;

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     * @Groups("json")
     */
    private $doi;

    public function getDoi(): ?string
    {
        return $this->doi;
    }

    public function setDoi(?string $doi): self
    {
        $this->doi = $doi;

        return $this;
    }

    /**
     * @Assert\NotBlank(groups={"create"}, message="Please attach a PDF file.")
     * @Assert\File(
     *     mimeTypes = {"application/pdf"},
     *     mimeTypesMessage = "Please upload a valid PDF file."
     * )
     *
     * This property is used when uploading the file; once uploaded it is stored in the `uploads`
     * directory, rather than in the database. A file must be included when a new Article is
     * created; subsequent editing need not alter that file.
     */
    private $file;

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @Groups("json")
     */
    private $filename;

    public function getFilename(): ?string
    {
        return $this->museId ? $this->museId.'.pdf' : $this->title.'.pdf';
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }
}
