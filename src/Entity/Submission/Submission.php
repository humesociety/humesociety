<?php

namespace App\Entity\Submission;

use App\Entity\Conference\Conference;
use App\Entity\Review\Review;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A submission for the Hume Conference.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Submission\SubmissionRepository")
 * @UniqueEntity(
 *     fields={"user", "conference"},
 *     errorPath="conference",
 *     message="You have already submitted a paper for this conference."
 * )
 */
class Submission
{
    /**
     * The submission's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Get the submission's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The user who submitted the paper.
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="submissions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Get the user who submitted the paper (null when the object is first created).
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user who submitted the paper.
     *
     * @param User The user who submitted the paper.
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * The conference the paper is submitted to.
     *
     * @var Conference
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference\Conference", inversedBy="submissions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conference;

    /**
     * Get the conference this paper is submitted to (null when the object is first created).
     *
     * @return Conference
     */
    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    /**
     * Set the conference the paper is submitted to.
     *
     * @param Conference The conference the paper is submitted to.
     * @return self
     */
    public function setConference(Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    /**
     * The date the submission is created/submitted.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="date")
     */
    private $dateSubmitted;

    /**
     * Get the date the submission is created/submitted.
     *
     * @return \DateTimeInterface
     */
    public function getDateSubmitted(): \DateTimeInterface
    {
        return $this->dateSubmitted;
    }

    /**
     * The reviews of this submission.
     *
     * @var Review[]
     * @ORM\OneToMany(targetEntity="App\Entity\Review\Review", mappedBy="submission")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reviews;

    /**
     * Get the reviews of this submission.
     *
     * @return Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     * The title of the paper.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * Get the title of the paper (null when the object is first created).
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title of the paper.
     *
     * @param string The title of the paper.
     * @return self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * The abstract of the paper.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $abstract;

    /**
     * Get the abstract of the paper (null when the object is first created).
     *
     * @return string|null
     */
    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    /**
     * Set the abstract of the paper.
     *
     * @param string The abstract of the paper.
     * @return self
     */
    public function setAbstract(string $abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * The keywords for the paper (as entered by the author/submitter).
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $keywords;

    /**
     * Get the keywords for the paper (null when the object is first created).
     *
     * @return string|null
     */
    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    /**
     * Set the keywords for the paper.
     *
     * @param string The keywords for the paper.
     * @return self
     */
    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * The name of the submission file.
     *
     * @var string
     * @ORM\Column(type="string", length=5)
     */
    private $filename;

    /**
     * Get the name of the submission file (null when the object is first created).
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * The status of the submission (submitted|accepted|rejected).
     *
     * @var string
     * @ORM\Column(type="string", length=16)
     * submitted|accepted|rejected
     */
    private $status;

    /**
     * Get the status of the submission (null when the object is first created).
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Set the status of the submission.
     *
     * @param string The status of the submission.
     * @return self
     */
    public function setStatus(string $status)
    {
        if (in_array($status, ['submitted', 'accepted', 'rejected'])) {
            $this->status = $status;
        }

        return $this;
    }

    /**
     * The file submitted.
     *
     * This property is used when uploading the file; once uploaded it is stored in the `uploads`
     * directory, rather than in the database. A file must be included when a new Submission is
     * created; subsequent editing need not alter that file.
     *
     * @var UploadedFile|null
     * @Assert\NotBlank(groups={"create"}, message="Please attach a PDF file.")
     * @Assert\File(
     *     mimeTypes = {
     *          "application/msword",
     *          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *          "application/rtf"
     *     },
     *     mimeTypesMessage = "Please upload your paper in Word or RTF format."
     * )
     */
    private $file;

    /**
     * Get the file submitted.
     *
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Set the file submitted (and the submission's filename at the same time).
     *
     * @param UpladedFile The file submitted.
     * @return self
     */
    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;
        $this->filename = $file->getClientOriginalName();

        return $this;
    }

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        $this->dateSubmitted = new \DateTime();
        $this->reviews = new ArrayCollection();
        $this->status = 'submitted';
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
