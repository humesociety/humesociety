<?php

namespace App\Entity\Issue;

use App\Entity\Article\Article;
use App\Entity\Note\Note;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Issue\IssueRepository")
 * @UniqueEntity(
 *     fields={"volume", "number"},
 *     errorPath="volume",
 *     message="There is already an issue with this volume and number."
 * )
 *
 * An issue of Hume Studies. Contains Articles and Notes, both of which are persisted entities.
 * Articles can be articles or book reviews; Notes are for miscellaneous front and back matter.
 */
class Issue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups("json")
     */
    private $volume;

    /**
     * @ORM\Column(type="integer")
     * @Groups("json")
     *
     * Typically each volume has two issues, numbered 1 and 2. Some volumes only have one issue;
     * these should be numbered 0. Some volumes have a supplementary issue (e.g. the 10th
     * anniversary special edition in volume 11); these should be numbered 3.
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $name;

    /**
    * @ORM\Column(type="integer")
    * @Groups("json")
     */
    private $museId;

    /**
    * @ORM\Column(type="string", length=255)
    * @Groups("json")
     */
    private $editors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article\Article", mappedBy="issue")
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups("json")
     */
    private $articles;

    /**
     * @Groups("json")
     */
    private $month;

    /**
     * @Groups("json")
     */
    private $year;

    // Constructor function
    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    // ToString function
    public function __toString(): string
    {
        return 'volume '.$this->volume.', number '.$this->number;
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVolume(): ?int
    {
        return $this->volume;
    }

    public function setVolume(int $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMuseId(): ?int
    {
        return $this->museId;
    }

    public function setMuseId(int $museId): self
    {
        $this->museId = $museId;

        return $this;
    }

    public function getEditors(): ?string
    {
        return $this->editors;
    }

    public function setEditors(string $editors): self
    {
        $this->editors = $editors;

        return $this;
    }

    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setIssue($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getIssue() === $this) {
                $article->setIssue(null);
            }
        }

        return $this;
    }

    // Getters for derivative properties
    public function getYear(): ?int
    {
        return $this->volume + 1974;
    }

    public function getDecade(): ?int
    {
        return $this->getYear() - ($this->getYear() % 10);
    }

    public function getMonth(): ?string
    {
        if ($this->number == 0) {
            return 'April/November';
        }
        if ($this->number == 1) {
            return 'April';
        }
        if ($this->number == 2) {
            return 'November';
        }
        if ($this->number == 3) {
            return $this->name;
        }
    }
}
