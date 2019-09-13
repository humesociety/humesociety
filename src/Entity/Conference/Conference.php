<?php

namespace App\Entity\Conference;

use App\Entity\Upload\Upload;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Conference\ConferenceRepository")
 * @UniqueEntity(
 *     fields="number",
 *     message="There is already a conference with this number in the database."
 * )
 * @UniqueEntity(
 *     fields="year",
 *     message="There is already a conference for this year in the database."
 * )
 *
 * Conference objects represent Hume Society conferences. There can be no more than one each year
 * (and typically exactly one, but some years there hasn't been any). Note that, though the start
 * and end dates include the year, the separate year field is not redundant; the year may be known
 * before the precise dates, and the start and end date fields are nullable.
 *
 * Files uploaded to the 'uploads/conferences/{year}' directory are implicitly associated with the
 * conference for that {year}. Files with specific names (e.g. 'CFP.pdf', 'index.html') are
 * implicitly assinged a particular association (e.g. call for papers, web site home page). See the
 * final getters at the end of this class definition for details.
 */
class Conference
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $number;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $year;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $institution;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $town;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * uploads are stored on disk rather than in the database; the conference handler should
     * populate this array when returning conferences (by reading from the disk)
     */
    private $uploads;

    // Constructor function
    // not needed

    // ToString function
    public function __toString(): string
    {
        return 'number '.$this->number.' ('.$this->year.')';
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getUploads(): array
    {
        return $this->uploads;
    }

    public function setUploads(array $uploads): self
    {
        $this->uploads = $uploads;

        return $this;
    }

    // Getters for derivative properies
    public function getOrdinal(): ?string
    {
        if (($this->number % 100) >= 11 && ($this->number % 100) <= 13) {
            return $this->number.'th';
        }
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        return $this->number.$ends[$this->number % 10];
    }

    public function getDecade(): ?int
    {
        return $this->year - ($this->year % 10);
    }

    public function getDates(): ?string
    {
        if ($this->startDate && $this->endDate) {
            return date_format($this->startDate, 'F j').' - '.date_format($this->endDate, 'F j').', '.$this->year;
        }

        return $this->year;
    }

    public function getSplash(): ?Upload
    {
        foreach ($this->uploads as $upload) {
            if (in_array($upload->getFilename(), ['splash.jpg', 'splash.gif', 'splash.png'])) {
                return $upload;
            }
        }

        return null;
    }

    public function getCfp(): ?Upload
    {
        foreach ($this->uploads as $upload) {
            if (in_array($upload->getFilename(), ['CFP.pdf', 'cfp.html'])) {
                return $upload;
            }
        }

        return null;
    }

    public function getProgram(): ?Upload
    {
        foreach ($this->uploads as $upload) {
            if (in_array($upload->getFilename(), ['program.pdf', 'program.html'])) {
                return $upload;
            }
        }

        return null;
    }

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
