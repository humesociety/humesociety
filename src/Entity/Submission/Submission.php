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
     * The user who submitted the paper.
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="submissions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * The conference the paper is submitted to.
     *
     * @var Conference
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference\Conference", inversedBy="submissions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conference;

    /**
     * The date the submission is created/submitted.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="date")
     */
    private $dateSubmitted;

    /**
     * The reviews of this submission.
     *
     * @var Review[]
     * @ORM\OneToMany(targetEntity="App\Entity\Review\Review", mappedBy="submission")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reviews;

    /**
     * The title of the paper.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * The authors of the paper.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $authors;

    /**
     * The abstract of the paper.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $abstract;

    /**
     * The keywords for the paper.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $keywords;

    /**
     * The name of the submission file.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * The status of the submission (submitted|accepted|rejected).
     *
     * @var string
     * @ORM\Column(type="string", length=16)
     */
    private $status;

    /**
     * The uploaded submission file (used temporarily when uploading the file).
     *
     * @var UploadedFile
     * @Assert\NotBlank(groups={"create"}, message="Please attach a file.")
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
     * Get the date the submission is created/submitted.
     *
     * @return \DateTimeInterface
     */
    public function getDateSubmitted(): \DateTimeInterface
    {
        return $this->dateSubmitted;
    }

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
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the authors of the paper (null when the object is first created).
     *
     * @return string|null
     */
    public function getAuthors(): ?string
    {
        return $this->authors;
    }

    /**
     * Set the authors of the paper.
     *
     * @param string The authors of the paper.
     * @return self
     */
    public function setAuthors(string $authors): self
    {
        $this->authors = $authors;

        return $this;
    }

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
    public function setAbstract(string $abstract): self
    {
        $this->abstract = $abstract;

        return $this;
    }

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
    public function setKeywords(string $keywords): self
    {
        // enforce regular comma-separated format
        $this->keywords = implode(', ', array_map('trim', explode(',', $keywords)));

        return $this;
    }

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
    public function setStatus(string $status): self
    {
        if (in_array($status, ['submitted', 'accepted', 'rejected'])) {
            $this->status = $status;
        }

        return $this;
    }

    /**
     * Get the submission file.
     *
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Set the submission file.
     *
     * @param UploadedFile The submission file.
     * @return self
     */
    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;
        $this->filename = $file->getClientOriginalName();

        return $this;
    }

    /**
     * Get path to this file in the uploads subdirectory (overwrite the Upload default).
     *
     * @return string
     */
    public function getPath(): string
    {
        return 'user'.$this->getUser()->getId().'/'.$this->getConference()->getNumber().'/';
    }

    /**
     * Get whether the given user has permission to view this submission.
     *
     * @return bool
     */
    public function userCanView(User $user): bool
    {
        if ($this->user == $user) {
            return true;
        }
        if (in_array('ROLE_ORGANISER', $user->getRoles())) {
            return true;
        }
        if (in_array('ROLE_TECH', $user->getRoles())) {
            return true;
        }
        // TODO: allow access to reviewers
        return false;
    }
}
