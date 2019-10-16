<?php

namespace App\Entity\Review;

use App\Entity\Reviewer\Reviewer;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * A review for a submission for the Hume Conference.
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"reviewer", "submission"},
 *     errorPath="reviewer",
 *     message="This reviewer has already been invited to review this paper."
 * )
 */
class Review
{
    /**
     * The review's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The submission being reviewed.
     *
     * @var Submission
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Submission\Submission",
     *     inversedBy="reviews",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $submission;

    /**
     * The reviewer.
     *
     * @var Reviewer
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Reviewer\Reviewer",
     *     inversedBy="reviews",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $reviewer;

    /**
     * The review's secret (randomly generated string for linking to the review).
     *
     * @var string
     * @ORM\Column(type="string", length=8)
     */
    private $secret;

    /**
     * Whether the reviewer accepts the invitation to review (null means reply pending).
     *
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accepted;

    /**
     * The date the review is submitted.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateSubmitted;

    /**
     * The reviewer's grade (A|B|C|D).
     *
     * @var string|null
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $grade;

    /**
     * The reviewer's comments.
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * Constructor function.
     *
     * @var Submission The submission being reviewed.
     * @return void
     */
    public function __construct(Submission $submission)
    {
        $this->id = null; // Doctrine takes care of this
        $this->submission = $submission;
        $this->reviewer = null;
        $this->secret = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 8; $i++) {
            $this->secret .= $characters[rand(0, strlen($characters) - 1)];
        }
        $this->accepted = null;
        $this->dateSubmitted = null;
        $this->grade = null;
        $this->comments = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "Review for “{$this->submission->getTitle()}”";
    }

    /**
     * Get the unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the submission being reviewed.
     *
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    /**
    * Get the reviewer (null when the object is first created).
    *
    * @return Reviewer|null
    */
    public function getReviewer(): ?Reviewer
    {
        return $this->reviewer;
    }

    /**
     * Set the reviewer.
     *
     * @var Reviewer The reviewer.
     * @return self
     */
    public function setReviewer(Reviewer $reviewer): self
    {
        $this->reviewer = $reviewer;
        return $this;
    }

    /**
     * Get the review's secret.
     *
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Get whether the reviewer accepts the invitation to review.
     *
     * @return bool|null
     */
    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    /**
     * Set whether the reviewer accepts the invitation to review.
     *
     * @var bool Whether the reviewer accepts the invitation to review.
     * @return self
     */
    public function setAccepted(bool $accepted)
    {
        $this->accepted = $accepted;
    }

    /**
     * Get the date the review is submitted.
     *
     * @return \DateTimeInferface|null
     */
    public function getDateSubmitted(): ?\DateTimeInterface
    {
        return $this->dateSubmitted;
    }

    /**
     * Set the date the review is submitted.
     *
     * @var \DateTimeInterface The date the review is submitted.
     * @return self
     */
    public function setDateSubmitted(\DateTimeInterface $dateSubmitted): self
    {
        $this->dateSubmitted = $dateSubmitted;
        return $this;
    }

    /**
     * Get the reviewer's grade.
     *
     * @return string|null
     */
    public function getGrade(): ?string
    {
        return $this->grade;
    }

    /**
     * Set the reviewer's grade.
     *
     * @var string The reviewer's grade.
     * @return self
     */
    public function setGrade(string $grade)
    {
        $this->grade = $grade;
        return $this;
    }

    /**
     * Get the reviewer's comments.
     *
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * Set the reviewer's comments.
     *
     * @var string The reviewer's comments.
     * @return self
     */
    public function setComments(string $comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * Get whether the review is submitted.
     *
     * @return bool
     */
    public function getSubmitted(): bool
    {
        return $this->dateSubmitted !== null;
    }

    /**
     * Get the review's status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        if ($this->accepted === true) {
            return $this->dateSubmitted ? 'submitted' : 'accepted';
        }
        if ($this->accepted === false) {
            return 'declined';
        }
        return 'pending';
    }

    /**
     * Get the link to this review.
     *
     * @return string
     */
    public function getLink(): string
    {
        return $this->getReviewer()->getSecret().'/'.$this->secret;
    }
}
