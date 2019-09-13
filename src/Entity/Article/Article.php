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
 *
 * An article (or book review) in an issue of Hume Studies.
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Issue\Issue", inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $issue;

    /**
     * @ORM\Column(type="integer")
     * @Groups("json")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $authors;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("json")
     */
    private $startPage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("json")
     */
    private $endPage;

    /**
     * @ORM\Column(type="integer", unique=true, nullable=true)
     * @Groups("json")
     */
    private $museId;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     * @Groups("json")
     */
    private $doi;

    /**
     * @Groups("json")
     */
    private $filename;

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

    // Constructor function
    // not needed

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

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function setIssue(Issue $issue): self
    {
        $this->issue = $issue;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getAuthors(): ?string
    {
        return $this->authors;
    }

    public function setAuthors(?string $authors)
    {
        $this->authors = $authors;
    }

    public function getStartPage(): ?int
    {
        return $this->startPage;
    }

    public function setStartPage(?int $startPage)
    {
        $this->startPage = $startPage;
    }

    public function getEndPage(): ?int
    {
        return $this->endPage;
    }

    public function setEndPage(?int $endPage)
    {
        $this->endPage = $endPage;
    }

    public function getMuseId(): ?int
    {
        return $this->museId;
    }

    public function setMuseId(?int $museId): self
    {
        $this->museId = $museId;

        return $this;
    }

    public function getDoi(): ?string
    {
        return $this->doi;
    }

    public function setDoi(?string $doi): self
    {
        $this->doi = $doi;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    // Getters for derivative properties
    public function getFilename(): ?string
    {
        return $this->museId ? $this->museId.'.pdf' : $this->title.'.pdf';
    }
}
