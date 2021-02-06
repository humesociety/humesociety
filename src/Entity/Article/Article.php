<?php

namespace App\Entity\Article;

use App\Entity\Issue\Issue;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An article (or book review) in an issue of Hume Studies.
 *
 * @ORM\Entity()
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
     * The issue the article is published in.
     *
     * @var Issue
     * @ORM\ManyToOne(targetEntity="App\Entity\Issue\Issue", inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $issue;

    /**
     * The position of the article in the issue.
     *
     * @var int
     * @ORM\Column(type="integer")
     * @Groups("json")
     */
    private $position;

    /**
     * The title of the article.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $title;

    /**
     * The authors of the article.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $authors;

    /**
     * The start page of the article.
     *
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("json")
     */
    private $startPage;

    /**
     * The end page of the article.
     *
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("json")
     */
    private $endPage;

    /**
     * The article's identifier on Project MUSE.
     *
     * @var int|null
     * @ORM\Column(type="integer", unique=true, nullable=true)
     * @Groups("json")
     */
    private $museId;

    /**
     * The article's DOI.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     * @Groups("json")
     */
    private $doi;

    /**
     * The file itself (a temporary property used when uploading the article).
     *
     * @var UploadedFile|null
     * @Assert\NotBlank(groups={"create"}, message="Please attach a file.")
     * @Assert\File(
     *     mimeTypes = {"application/pdf"},
     *     mimeTypesMessage = "Please upload the article in PDF format."
     * )
     */
    private $file;

    /**
     * The article's filename on disk.
     *
     * @var string
     * @Groups("json")
     */
    private $filename;

    /**
     * The path to the article's file on disk.
     *
     * @var string
     * @Groups("json")
     */
    private $path;

    /**
     * Constructor function.
     *
     * @param Issue $issue The issue the article is in.
     * @return void
     */
    public function __construct(Issue $issue)
    {
        // persisted properties
        $this->id= null;
        $this->issue = $issue;
        $this->position = null;
        $this->title = null;
        $this->authors = null;
        $this->startPage = null;
        $this->endPage = null;
        $this->museId = null;
        $this->doi = null;
        // temporary properties
        $this->file = null;
        // derivative properties
        $this->filename = null;
        $this->path = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->title ? $this->title : 'uninitialised article';
    }

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
     * Get the issue the article is published in.
     *
     * @return Issue
     */
    public function getIssue(): Issue
    {
        return $this->issue;
    }

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
     * @param int $position The position of the article in the issue.
     * @return self
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

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
     * @param string $title The title of the article.
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

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
     * @param string|null $authors The authors of the article.
     * @return self
     */
    public function setAuthors(?string $authors): self
    {
        $this->authors = $authors;
        return $this;
    }

    /**
     * Get the start page of the article.
     *
     * @return int|null
     */
    public function getStartPage(): ?int
    {
        return $this->startPage;
    }

    /**
     * Set the start page of the article.
     *
     * @param int|null $startPage The start page of the article.
     * @return self
     */
    public function setStartPage(?int $startPage): self
    {
        $this->startPage = $startPage;
        return $this;
    }

    /**
     * Get the end page of the article.
     *
     * @return int|null
     */
    public function getEndPage(): ?int
    {
        return $this->endPage;
    }

    /**
     * Set the end page of the article.
     *
     * @param int|null $endPage The end page of the article.
     * @return self
     */
    public function setEndPage(?int $endPage): self
    {
        $this->endPage = $endPage;
        return $this;
    }

    /**
     * Get the article's ID on Project MUSE.
     *
     * @return int|null
     */
    public function getMuseId(): ?int
    {
        return $this->museId;
    }

    /**
     * Set the article's ID on Project MUSE.
     *
     * @param int|null $museId The article's ID on Project MUSE.
     * @return self
     */
    public function setMuseId(?int $museId): self
    {
        $this->museId = $museId;
        return $this;
    }

    /**
     * Get the article's DOI.
     *
     * @return string|null
     */
    public function getDoi(): ?string
    {
        return $this->doi;
    }

    /**
     * Set the article's DOI.
     *
     * @param string|null $doi The article's DOI.
     * @return self
     */
    public function setDoi(?string $doi): self
    {
        $this->doi = $doi;
        return $this;
    }

    /**
     * Get the file itself.
     *
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Set the uploaded file.
     *
     * @param UploadedFile $file The file.
     * @return self
     */
    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get the article's filename on disk (null when the object is first created).
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        if ($this->museId) {
            return "{$this->museId}.pdf";
        }
        if ($this->title) {
            return "{$this->title}.pdf";
        }
        return null;
    }

    /**
     * Get the path to the article's file on disk.
     *
     * @return string
     */
    public function getPath(): string
    {
        return "issues/v{$this->issue->getVolume()}n{$this->issue->getNumber()}/";
    }
}
