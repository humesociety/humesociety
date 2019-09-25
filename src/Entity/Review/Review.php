<?php

namespace App\Entity\Review;

use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A review for a submission for the Hume Conference.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Review\ReviewRepository")
 * @UniqueEntity(
 *     fields={"user", "submission"},
 *     errorPath="submission",
 *     message="You have already submitted a review for this paper."
 * )
 */
class Review
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Submission\Submission", inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $submission;

    /**
     * @ORM\Column(type="string", length=16)
     * pending|accepted|submitted
     */
    private $status;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateSubmitted;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $grade; // A, B, C, or D

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     */
    private $submitted; // derivative property; dateSubmitted !== null

    // Constructor function
    public function __construct()
    {
        $this->status = 'pending';
    }

    // ToString function
    public function __toString(): string
    {
        return 'Review for "'.$this->submission->getTitle().'"';
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

    public function getSubmission(): ?Submission
    {
        return $this->submission;
    }

    public function setSubmission(Submission $submission): self
    {
        $this->submission = $submission;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status)
    {
        $this->accepted = $accepted;
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

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade)
    {
        $this->grade = $grade;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(string $comments)
    {
        $this->comments = $comments;
    }
}
