<?php

namespace App\Entity\Submission;

use App\Entity\Conference\Conference;
use App\Entity\Review\Review;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Submission\SubmissionRepository")
 * @UniqueEntity(
 *     fields={"user", "conference"},
 *     errorPath="conference",
 *     message="You have already submitted a paper for this conference."
 * )
 *
 * A submission for the Hume Conference.
 */
class Submission
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="submissions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference\Conference", inversedBy="submissions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conference;

    /**
     * @ORM\Column(type="date")
     */
    private $dateSubmitted;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Review\Review", mappedBy="submission")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reviews;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $keywords;

    /**
     * @ORM\Column(type="text")
     */
    private $abstract;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $ext;

    /**
     * @ORM\Column(type="string", length=16)
     * submitted|accepted|rejected
     */
    private $status;

    /**
     * @Assert\NotBlank(groups={"create"}, message="Please attach a PDF file.")
     * @Assert\File(
     *     mimeTypes = {
     *          "application/msword",
     *          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *          "application/rtf"
     *     },
     *     mimeTypesMessage = "Please upload your paper in Word or RTF format."
     * )
     *
     * This property is used when uploading the file; once uploaded it is stored in the `uploads`
     * directory, rather than in the database. A file must be included when a new Submission is
     * created; subsequent editing need not alter that file.
     */
    private $file;

    /**
     * derived property (`id`.`ext`)
     */
    private $filename;

    // Constructor function
    public function __construct()
    {
        $this->dateSubmitted = new \DateTime();
        $this->reviews = new ArrayCollection();
        $this->status = 'submitted';
    }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getDateSubmitted(): ?\DateTimeInterface
    {
        return $this->dateSubmitted;
    }

    public function setDateSubmitted(?\DateTimeInterface $dateSubmitted): self
    {
        $this->dateSubmitted = $dateSubmitted;

        return $this;
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setSubmission($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getSubmission() === $this) {
                $review->setSubmission(null);
            }
        }

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

    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    public function setAbstract(string $abstract)
    {
        $this->abstract = $abstract;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(string $ext)
    {
        $this->ext = $ext;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        if (in_array($status, ['submitted', 'accepted', 'rejected'])) {
            $this->status = $status;
        }
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;
        $this->ext = $file->guessClientExtension();

        return $this;
    }

    // Getters for derivative properties
    public function getFilename(): ?string
    {
        return $this->id.'.'.$this->ext;
    }

    public function getKeywordsArray(): array
    {
        return array_map('trim', explode(',', $this->keywords));
    }
}
