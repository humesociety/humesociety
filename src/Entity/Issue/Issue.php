<?php

namespace App\Entity\Issue;

use App\Entity\Article\Article;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An issue of Hume Studies.
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"volume", "number"},
 *     errorPath="volume",
 *     message="There is already an issue with this volume and number."
 * )
 */
class Issue
{
    /**
     * The issue's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The issue's volume.
     *
     * @var int
     * @ORM\Column(type="integer")
     * @Groups("json")
     */
    private $volume;

    /**
     * The issue's number.
     *
     * Typically each volume has two issues, numbered 1 and 2. Some volumes only have one issue;
     * these should be numbered 0. Some volumes have a supplementary issue (e.g. the 10th
     * anniversary special edition in volume 11); these should be numbered 3.
     *
     * @var int
     * @ORM\Column(type="integer")
     * @Groups("json")
     */
    private $number;

    /**
     * The issue's name (for special issues).
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $name;

    /**
     * The issue's identifier on Project MUSE.
     *
     * @var int
     * @ORM\Column(type="integer")
     * @Groups("json")
     */
    private $museId;

    /**
     * The issue's editors.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $editors;

    /**
     * The issue's articles.
     *
     * @var Article[]
     * @ORM\OneToMany(targetEntity="App\Entity\Article\Article", mappedBy="issue")
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups("json")
     */
    private $articles;

    /**
     * The year the issue was officially published (derived from its volume).
     *
     * @var int
     * @Groups("json")
     */
    private $year;

    /**
     * The decade the issue was officially published (derived from its year).
     *
     * @var int
     * @Groups("json")
     */
    private $decade;

    /**
     * The month the issue was officially published (derived from its number).
     *
     * @var string
     * @Groups("json")
     */
    private $month;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        // persisted properties
        $this->id = null; // doctrine takes care of this
        $this->volume = null;
        $this->number = null;
        $this->name = null;
        $this->museId = null;
        $this->editors = null;
        $this->articles = new ArrayCollection();
        // derivative properties
        $this->year = null;
        $this->decade = null;
        $this->month = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return ($this->volume && $this->number)
            ? "volume {$this->volume}, number {$this->number}"
            : 'uninitialised issue';
    }

    /**
     * Get the issue's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the issue's volume (null when the object is first created).
     *
     * @return int|null
     */
    public function getVolume(): ?int
    {
        return $this->volume;
    }

    /**
     * Set the issue's volume.
     *
     * @param int $volume The issue's volume.
     * @return self
     */
    public function setVolume(int $volume): self
    {
        $this->volume = $volume;
        return $this;
    }

    /**
     * Get the issue's number (null when the object is first created).
     *
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * Set the issue's number.
     *
     * @param int $number The issue's number.
     * @return self
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;
        return $this;
    }

    /**
     * Get the issue's name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the issue's name.
     *
     * @param string|null $name The issue's name.
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the issue's identifier on Project MUSE (null when the object is first created).
     *
     * @return int|null
     */
    public function getMuseId(): ?int
    {
        return $this->museId;
    }

    /**
     * Set the issue's identifier on Project MUSE.
     *
     * @param int $mudeId The issue's identifier on Project MUSE.
     * @return self
     */
    public function setMuseId(int $museId): self
    {
        $this->museId = $museId;
        return $this;
    }

    /**
     * Get the issue's editors (null when the object is first created).
     *
     * @return string|null
     */
    public function getEditors(): ?string
    {
        return $this->editors;
    }

    /**
     * Set the issue's editors.
     *
     * @param string $editors The issue's editors.
     * @return self
     */
    public function setEditors(string $editors): self
    {
        $this->editors = $editors;
        return $this;
    }

    /**
     * Get the issue's articles.
     *
     * @return Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /**
     * Get the issue's official publication year (null when the object is first created).
     *
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->volume ? $this->volume + 1974 : null;
    }

    /**
     * Get the issue's official publication decade (null when the object is first created).
     *
     * @return int|null
     */
    public function getDecade(): ?int
    {
        return $this->getYear() ? $this->getYear() - ($this->getYear() % 10) : null;
    }

    /**
     * Get the issue's official publication month (null when the object is first created).
     *
     * @return string|null
     */
    public function getMonth(): ?string
    {
        $months = ['April/November', 'April', 'November', $this->name];
        return ($this->number !== null) ? $months[$this->number] : null;
    }
}
