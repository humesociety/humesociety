<?php

namespace App\Entity\Review;

use App\Entity\Invitation\Invitation;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Submission\Submission", inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $submission;

    /**
     * The user invited to review.
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="reviews")
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
     * The reviewer's comments as shown to the author - potentially edited
     * by the conference organisers.
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentsForAuthor;

    /**
     * Constructor function.
     *
     * @var Submission $submission The submission being reviewed.
     * @throws \Exception
     * @return void
     */
    public function __construct(Submission $submission)
    {
        // invitation properties
        parent::__construct();
        // persisted properties
        $this->id = null; // Doctrine takes care of this
        $this->submission = $submission;
        $this->user = null;
        $this->grade = null;
        $this->comments = null;
        $this->commentsForAuthor = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "Review for {$this->submission}";
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
     * @var User $user The user invited to review.
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
     * Get the reviewer's grade as a integer between 1 and 4.
     *
     * @return int|null
     */
    public function getGradeNumber(): ?int
    {
        switch ($this->grade) {
            case 'A':
                return 4;
            case 'B':
                return 3;
            case 'C':
                return 2;
            case 'D':
                return 1;
            case null:
                return null;
        }
    }

    /**
     * Set the reviewer's grade.
     *
     * @var string $grade The reviewer's grade.
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
     * @var string $comments The reviewer's comments.
     * @return self
     */
    public function setComments(string $comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * Get the reviewer's comments as shown to the author.
     *
     * @return string|null
     */
    public function getCommentsForAuthor(): ?string
    {
        return $this->commentsForAuthor ? $this->commentsForAuthor : $this->comments;
    }

    /**
     * Set the reviewer's comments as shown to the author.
     *
     * @var string $commentsForAuthor The reviewer's comments as show to the author.
     * @return self
     */
    public function setCommentsForAuthor(string $commentsForAuthor)
    {
        $this->commentsForAuthor = $commentsForAuthor;
        return $this;
    }
}
