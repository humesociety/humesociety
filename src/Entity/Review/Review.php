<?php

namespace App\Entity\Review;

use App\Entity\Invitation\Invitation;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * A review for a submission to the Hume Conference (or an invitation to review).
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"submission", "user"},
 *     errorPath="user",
 *     message="This person has already been invited to review this paper."
 * )
 */
class Review extends Invitation
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
     * The user invited to review.
     *
     * @var User
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\User",
     *     inversedBy="reviews",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
        $this->user = null;
        parent::__construct();
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
    * Get the user invited to review (null when the object is first created).
    *
    * @return User|null
    */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user invited to review.
     *
     * @var User The user invited to review.
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
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
}
