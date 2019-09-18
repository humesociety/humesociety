<?php

namespace App\Entity\Conference;

use App\Entity\Submission\Submission;
use App\Entity\Upload\Upload;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Conference objects represent Hume Society conferences.
 *
 * There can be no more than one conference each year (and typically there is exactly one, but some
 * years there hasn't been any). Note that, though the start and end dates include the year, the
 * separate year field is not redundant; the year may be known before the precise dates are set, and
 * consequently the start and end date fields are nullable.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Conference\ConferenceRepository")
 * @UniqueEntity(
 *     fields="number",
 *     message="There is already a conference with this number in the database."
 * )
 * @UniqueEntity(
 *     fields="year",
 *     message="There is already a conference for this year in the database."
 * )
 */
class Conference
{
    /**
     * The conference's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Get the conference's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The conference's (unique) number.
     *
     * @var int
     * @ORM\Column(type="integer", unique=true)
     */
    private $number;

    /**
     * Get the conference's (unique) number (null when the object is first created).
     *
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * Set the conference's (unique) number.
     *
     * @param int The conference's (unique) number.
     * @return self
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * The year of the conference.
     *
     * @var int
     * @ORM\Column(type="integer", unique=true)
     */
    private $year;

    /**
     * Get the year of the conference (null when the object is first created).
     *
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * Set the year of the conference.
     *
     * @param int The year of the conference.
     * @return self
     */
    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    /**
     * The start date of the conference.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * Get the start date of the conference.
     *
     * @return \DateTimeInterface|null
     */
    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * Set the start date of the conference.
     *
     * @param \DateTimeInterface|null The start date of the conference.
     * @return self
     */
    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * The end date of the conference.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * Get the end date of the conference.
     *
     * @return \DateTimeInterface|null
     */
    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * Set the end date of the conference.
     *
     * @param \DateTimeInterface|null The end date of the conference.
     * @return self
     */
    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * The host institution for the conference.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $institution;

    /**
     * Get the host institution for the conference (null when the object is first created).
     *
     * @return string|null
     */
    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    /**
     * Set the host institution for the conference.
     *
     * @param string The host institution for the conference.
     * @return self
     */
    public function setInstitution(string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * The town where the conference is held.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $town;

    /**
     * Get the town where the conference is held (null when the object is first created).
     *
     * @return string|null
     */
    public function getTown(): ?string
    {
        return $this->town;
    }

    /**
     * Set the town where the conference is held.
     *
     * @param string The town where the conference is held.
     * @return self
     */
    public function setTown(string $town): self
    {
        $this->town = $town;

        return $this;
    }

    /**
     * The three-letter country code of the country where the conference is held.
     *
     * @var string
     * @ORM\Column(type="string", length=3)
     */
    private $country;

    /**
     * Get the three-letter country code of the country where the conference is held (null when the
     * object is first created).
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set the three-letter country code of the country where the conference is held.
     *
     * @param string The three-letter country code of the country where the conference is held.
     * @return self
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * The URL of the conference's web site.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * Get the URL of the conference's web site.
     *
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * Set the URL of the conference's web site.
     *
     * @param string|null
     * @return self
     */
    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    /**
     * The date at which submissions to this conference close.
     *
     * A null value for this property indicates that submissions have not yet been opened.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $deadline;

    /**
     * Get the date at which submissions to this conference close.
     *
     * @return \DateTimeInterface|null
     */
    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    /**
     * Set the date at which submissions to this conference close.
     *
     * @param \DateTimeInterface|null
     * @return self
     */
    public function setDeadline(?\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * A collection of submissions to this conference.
     *
     * @var Submission[]
     * @ORM\OneToMany(targetEntity="App\Entity\Submission\Submission", mappedBy="conference")
     * @ORM\JoinColumn(nullable=false)
     */
    private $submissions;

    /**
     * Get the collection of submissions to this conference.
     *
     * @return Collection
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    /**
     * Add a submission to this conference.
     *
     * @param Submission The submission entity to add.
     * @return self
     */
    public function addSubmission(Submission $submission): self
    {
        if (!$this->submissions->contains($submission)) {
            $this->submissions[] = $submission;
            $submission->setConference($this);
        }

        return $this;
    }

    /**
     * Remove a submission from this conference.
     *
     * @param Submission The submission entity to remove.
     * @return self
     */
    public function removeSubmission(Submission $submission): self
    {
        if ($this->submissions->contains($submission)) {
            $this->submissions->removeElement($submission);
            // set the owning side to null (unless already changed)
            if ($submission->getConference() === $this) {
                $submission->setConference(null);
            }
        }

        return $this;
    }

    /**
     * An array of uploads associated with this conference.
     *
     * Note that uploads are not persisted to the database, but simply saved to disk. The
     * ConferenceHandler should set this array when fetching Conferences from the database, by
     * reading from the appropriate directory on the disk.
     *
     * @var Upload[]
     */
    private $uploads;

    /**
     * Get the array of uploads associated with this conference.
     *
     * @return Upload[]
     */
    public function getUploads(): array
    {
        return $this->uploads;
    }

    /**
     * Set the array of uploads associated with this conference.
     *
     * @param Upload[] The array of uploads.
     * @return self
     */
    public function setUploads(array $uploads): self
    {
        $this->uploads = $uploads;

        return $this;
    }

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        // initialise the uploads as an array
        $this->uploads = [];
        // initialise the submissions as a Symfony ArrayCollection
        $this->submissions = new ArrayCollection();
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getOrdinal().' Hume Conference';
    }

    /**
     * Get the ordinal of this conference (from its number).
     *
     * @return string
     */
    public function getOrdinal(): string
    {
        if (($this->number % 100) >= 11 && ($this->number % 100) <= 13) {
            return $this->number.'th';
        }

        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        return $this->number.$ends[$this->number % 10];
    }

    /**
     * Get the decade of this conference (from its year).
     *
     * @return int
     */
    public function getDecade(): int
    {
        return $this->year - ($this->year % 10);
    }

    /**
     * Get a string representation of the dates of this conference.
     *
     * @return string
     */
    public function getDates(): string
    {
        if ($this->startDate && $this->endDate) {
            return date_format($this->startDate, 'F j').' - '.date_format($this->endDate, 'F j').', '.$this->year;
        }

        return $this->year;
    }

    /**
     * Get whether submissions are open.
     *
     * @return bool
     */
    public function submissionsAreOpen(): bool
    {
        return $this->deadline && $this->deadline >= new \DateTime('today');
    }

    /**
     * Get the splash image for this conference (if any).
     *
     * @return Upload|null
     */
    public function getSplash(): ?Upload
    {
        foreach ($this->uploads as $upload) {
            if (in_array($upload->getFilename(), ['splash.jpg', 'splash.gif', 'splash.png'])) {
                return $upload;
            }
        }

        return null;
    }

    /**
     * Get the CFP file for this conference (if any).
     *
     * @return Upload|null
     */
    public function getCfp(): ?Upload
    {
        foreach ($this->uploads as $upload) {
            if (in_array($upload->getFilename(), ['CFP.pdf', 'cfp.html'])) {
                return $upload;
            }
        }

        return null;
    }

    /**
     * Get the program file for this conference (if any).
     *
     * @return Upload|null
     */
    public function getProgram(): ?Upload
    {
        foreach ($this->uploads as $upload) {
            if (in_array($upload->getFilename(), ['program.pdf', 'program.html'])) {
                return $upload;
            }
        }

        return null;
    }

    /**
     * Get the home page file for this conference (if any).
     *
     * @return Upload|null
     */
    public function getSite(): ?Upload
    {
        foreach ($this->uploads as $upload) {
            if (in_array($upload->getFilename(), ['index.html'])) {
                return $upload;
            }
        }

        return null;
    }
}
