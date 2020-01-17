<?php

namespace App\Entity\Submission;

use App\Entity\Chair\Chair;
use App\Entity\Comment\Comment;
use App\Entity\Conference\Conference;
use App\Entity\Review\Review;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A submission for the Hume Conference.
 *
 * @ORM\Entity()
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
     * The name of the INITIAL uploaded file.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * The name of the FINAL uploaded file.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $finalFilename;

    /**
     * Whether the submission is accepted (null means decision pending; false means rejected).
     *
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accepted;

    /**
     * The date the user was emailed informing them of the decision (null if not yet emailed).
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDecisionEmailed;

    /**
     * The number of submission reminder emails sent.
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $submissionReminderEmails;

    /**
     * The date the last submission reminder email was sent.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateLastSubmissionReminderSent;

    /**
     * The reviews of the submission.
     *
     * @var Review[]
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Review\Review",
     *     mappedBy="submission",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $reviews;

    /**
     * The (invited) comments on the submission.
     *
     * @var Comment[]
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Comment\Comment",
     *     mappedBy="submission",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $comments;

    /**
     * The (invited) chairs for the session.
     *
     * @var Chair[]
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Chair\Chair",
     *     mappedBy="submission",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $chairs;

    /**
     * The INITIAL uploaded file.
     *
     * @var UploadedFile|null
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
     * The FINAL uploaded file.
     *
     * @var UploadedFile|null
     * @Assert\NotBlank(groups={"final"}, message="Please attach a file.")
     * @Assert\File(
     *     mimeTypes = {
     *          "application/msword",
     *          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *          "application/rtf"
     *     },
     *     mimeTypesMessage = "Please upload your paper in Word or RTF format."
     * )
     */
    private $finalFile;

    /**
     * The path to the files.
     *
     * @var string
     */
    private $path;

    /**
     * The accepted comment invitation.
     *
     * @var Comment
     */
    private $comment;

    /**
     * The accepted chair invitation.
     *
     * @var Comment
     */
    private $chair;

    /**
     * Whether the decision email has been sent.
     *
     * @var bool
     */
    private $decisionEmailed;

    /**
     * Whether the final version has been submitted.
     *
     * @var bool
     */
    private $submitted;

    /**
     * The status of the submission (pending|accepted|rejected|submitted).
     *
     * 'submitted' means the FINAL version is submitted; 'pending' means the decision is pending
     *
     * @var bool
     */
    private $status;

    /**
     * An icon representing the status.
     *
     * @var string
     */
    private $statusIcon;

    /**
     * Constructor function.
     *
     * @param User $user The user who submitted the paper.
     * @param Conference $conference The conference the paper is submitted to.
     * @throws \Exception
     * @return void
     */
    public function __construct(User $user, Conference $conference)
    {
        // persisted properties
        $this->id = null; // doctrine will take care of this
        $this->user = $user;
        $this->conference = $conference;
        $this->dateSubmitted = new \DateTime('today');
        $this->title = null;
        $this->authors = null;
        $this->abstract = null;
        $this->keywords = null;
        $this->filename = null;
        $this->finalFilename = null;
        $this->accepted = null;
        $this->dateDecisionEmailed = null;
        $this->submissionReminderEmails = 0;
        $this->dateLastSubmissionReminderSent = null;
        // relations
        $this->reviews = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->chairs = new ArrayCollection();
        // temporary properties
        $this->file = null;
        $this->finalFile = null;
        // derivative properties
        $this->path = null;
        $this->comment = null;
        $this->chair = null;
        $this->decisionEmailed = false;
        $this->submitted = null;
        $this->status = null;
        $this->statusIcon = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->title ? "“{$this->title}”" : 'uninitialised submission';
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
     * Get the user who submitted the paper.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get the conference this paper is submitted to.
     *
     * @return Conference
     */
    public function getConference(): Conference
    {
        return $this->conference;
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
     * @param string $title The title of the paper.
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
     * @param string $authors The authors of the paper.
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
     * @param string $abstract The abstract of the paper.
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
     * @param string $keywords The keywords for the paper.
     * @return self
     */
    public function setKeywords(string $keywords): self
    {
        // enforce regular comma-separated format
        $this->keywords = implode(', ', array_map('trim', explode(',', $keywords)));
        return $this;
    }

    /**
     * Get the name of the INITIAL uploaded file (null when the object is first created).
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Get the name of the FINAL uploaded file (null when the object is first created).
     *
     * @return string|null
     */
    public function getFinalFilename(): ?string
    {
        return $this->finalFilename;
    }

    /**
     * Get the review invitations for this submission.
     *
     * @return Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     * Get the comment invitations for this submission.
     *
     * @return Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Get the chair invitations for the submission.
     *
     * @return Chair[]
     */
    public function getChairs(): Collection
    {
        return $this->chairs;
    }

    /**
     * Get whether the submission is accepted (null means the decision is pending).
     *
     * @return bool|null
     */
    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    /**
     * Set whether the submission is accepted.
     *
     * @param bool|null $accepted Whether the submission is accepted.
     * @return self
     */
    public function setAccepted(?bool $accepted): self
    {
        $this->accepted = $accepted;
        return $this;
    }

    /**
     * Get the date when the user was emailed the decision (if any).
     *
     * @return \DateTimeInterface|null
     */
    public function getDateDecisionEmailed(): ?\DateTimeInterface
    {
        return $this->dateDecisionEmailed;
    }

    /**
     * Set the date when the user was emailed the decision (set it to today's date).
     *
     * @throws \Exception
     * @return self
     */
    public function setDateDecisionEmailed(): self
    {
        $this->dateDecisionEmailed = new \DateTime('today');
        return $this;
    }

    /**
     * Get whether the user has been emailed the decision.
     *
     * @return bool
     */
    public function getDecisionEmailed(): bool
    {
        return $this->dateDecisionEmailed !== null;
    }

    /**
     * Get the number of final submission reminder emails sent.
     *
     * @return int
     */
    public function getSubmissionReminderEmails(): int
    {
        return $this->submissionReminderEmails;
    }

    /**
     * Increment the number of final submission reminder emails sent.
     *
     * @throws \Exception
     * @return self
     */
    public function incrementSubmissionReminderEmails(): self
    {
        $this->submissionReminderEmails += 1;
        $this->dateLastSubmissionReminderSent = new \DateTime('today');
        return $this;
    }

    /**
     * Get the date the last final submission reminder email was sent.
     *
     * @return \DateTimeInterface|null
     */
    public function getDateLastSubmissionReminderSent(): ?\DateTimeInterface
    {
        return $this->dateLastSubmissionReminderSent;
    }

    /**
     * Get the INITIAL uploaded file.
     *
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Set the INITIAL uploaded file (and the filename at the same time).
     *
     * @param UploadedFile|null $file The INITIAL uploaded file.
     * @return self
     */
    public function setFile(?UploadedFile $file): self
    {
        if ($file !== null) {
            $this->filename = $file->getClientOriginalName();
        }
        $this->file = $file;
        return $this;
    }

    /**
     * Get the FINAL uploaded file.
     *
     * @return UploadedFile|null
     */
    public function getFinalFile(): ?UploadedFile
    {
        return $this->finalFile;
    }

    /**
     * Set the FINAL uploaded file (and the filename at the same time).
     *
     * @param UploadedFile|null $finalFile The FINAL uploaded file.
     * @return self
     */
    public function setFinalFile(?UploadedFile $finalFile): self
    {
        if ($finalFile !== null) {
            $this->finalFilename = $finalFile->getClientOriginalName();
        }
        $this->finalFile = $finalFile;
        return $this;
    }

    /**
     * Get path to the files fpr this submission in the uploads subdirectory.
     *
     * @return string
     */
    public function getPath(): string
    {
        return "submissions/user{$this->getUser()->getId()}/{$this->getConference()->getNumber()}/";
    }

    /**
     * Get the accepted comment invitation for this submission.
     *
     * @return Comment|null
     */
    public function getComment(): ?Comment
    {
        foreach ($this->comments as $comment) {
            if ($comment->isAccepted()) {
                return $comment;
            }
        }
        return null;
    }

    /**
     * Get the accepted chair invitation for this submission.
     *
     * @return Chair|null
     */
    public function getChair(): ?Chair
    {
        foreach ($this->chairs as $chair) {
            if ($chair->isAccepted()) {
                return $chair;
            }
        }
        return null;
    }

    /**
     * Get whether the user has submitted the final version.
     *
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->finalFilename !== null;
    }

    /**
     * Get the status of the submission.
     *
     * @return string
     */
    public function getStatus(): string
    {
        if ($this->finalFilename !== null) {
            return 'submitted';
        }
        if ($this->accepted === true) {
            return 'accepted';
        }
        if ($this->accepted === false) {
            return 'rejected';
        }
        return 'pending';
    }

    /**
     * Get the status icon.
     *
     * @return string
     */
    public function getStatusIcon(): string
    {
        switch ($this->getStatus()) {
            case 'submitted':
                return 'fas fa-check-double';

            case 'accepted':
                return 'fas fa-check';

            case 'rejected':
                return 'fas fa-times';

            case 'pending':
                return 'fas fa-hourglass-half';
        }
    }

    /**
     * Get the progress of the submission through the review process.
     *
     * @return string
     */
    public function getReviewProgress(): string
    {
        $invited = 0;
        $accepted = 0;
        $submitted = 0;
        foreach ($this->reviews as $review) {
            if ($review->getStatus() !== 'declined') {
                $invited += 1;
            }
            if ($review->isAccepted()) {
                $accepted += 1;
            }
            if ($review->isSubmitted()) {
                $submitted += 1;
            }
        }
        if ($invited < 2) {
            return 'invitedLT2';
        }
        if ($accepted < 2) {
            return 'acceptedLT2';
        }
        if ($submitted < 2) {
            return 'submittedLT2';
        }
        return 'submitted2';
    }

    /**
     * Get the progress of the submission through the commentator assignment process.
     *
     * @return string
     */
    public function getCommentProgress(): string
    {
        $invited = 0;
        $accepted = 0;
        $submitted = 0;
        foreach ($this->comments as $comment) {
            if ($comment->getStatus() !== 'declined') {
                $invited += 1;
            }
            if ($comment->isAccepted()) {
                $accepted += 1;
            }
            if ($comment->isSubmitted()) {
                $submitted += 1;
            }
        }
        if ($invited < 1) {
            return 'invitedLT1';
        }
        if ($accepted < 1) {
            return 'acceptedLT1';
        }
        if ($submitted < 1) {
            return 'submittedLT1';
        }
        return 'submitted1';
    }

    /**
     * Get the progress of the submission through the commentator assignment process.
     *
     * @return string
     */
    public function getChairProgress(): string
    {
        $invited = 0;
        $accepted = 0;
        foreach ($this->chairs as $chair) {
            if ($chair->getStatus() !== 'declined') {
                $invited += 1;
            }
            if ($chair->isAccepted()) {
                $accepted += 1;
            }
        }
        if ($invited < 1) {
            return 'invitedLT1';
        }
        if ($accepted < 1) {
            return 'acceptedLT1';
        }
        return 'accepted1';
    }

    /**
     * Get whether the given user has permission to view this submission.
     *
     * @param User|null $user The currently logged in user (if any).
     * @param string|null $secret The secret provided (if any).
     * @return bool
     */
    public function userCanView(?User $user, ?string $secret): bool
    {
        if ($user) {
            // anyone can view their own submissions
            if ($this->user === $user) {
                return true;
            }
            // the conference organisers can view any submission
            if (in_array('ROLE_ORGANISER', $user->getRoles())) {
                return true;
            }
            // the technical director can view any submission
            if (in_array('ROLE_TECH', $user->getRoles())) {
                return true;
            }
        }
        if ($secret) {
            foreach ($this->reviews as $review) {
                // the secret from any accepted review is ok
                if ($review->getSecret() === $secret && $review->isAccepted()) {
                    return true;
                }
            }
        }
        // otherwise no
        return false;
    }

    /**
     * Get average review grade as a number between 1 and 4.
     *
     * @return int|null
     */
    public function getReviewGradeAverage(): ?int
    {
        $submittedReviews = 0;
        $gradeTotal = 0;
        foreach ($this->reviews as $review) {
            if ($review->getStatus() === 'submitted') {
                $submittedReviews += 1;
                $gradeTotal += $review->getGradeNumber();
            }
        }
        return ($submittedReviews > 0) ? ($gradeTotal / $submittedReviews) : null;
    }
}
