<?php

namespace App\Entity\Comment;

use App\Entity\Invitation\Invitation;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A comment on a submission to the Hume Conference (or an invitation to comment).
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"submission", "user"},
 *     errorPath="user",
 *     message="This person has already been invited to comment on this paper."
 * )
 */
class Comment extends Invitation
{
    /**
     * The comment's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The submission concerned.
     *
     * @var Submission
     * @ORM\ManyToOne(targetEntity="App\Entity\Submission\Submission", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $submission;

    /**
     * The user invited to comment.
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * The title of the comment.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * The uploaded file (used temporarily when uploading).
     *
     * @var UploadedFile|null
     * @Assert\NotBlank(groups={"create"}, message="Please attach a file.")
     * @Assert\File(
     *     mimeTypes = {
     *          "application/msword",
     *          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *          "application/rtf"
     *     },
     *     mimeTypesMessage = "Please upload your comments in Word or RTF format."
     * )
     */
    private $file;

    /**
     * The name of the file.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * Constructor function.
     *
     * @param Submission $submission The submission concerned.
     * @return void
     */
    public function __construct(Submission $submission)
    {
        // invitation properties
        parent::__construct();
        // persisted properties
        $this->id = null; // doctrine will take care of this
        $this->submission = $submission;
        $this->user = null;
        $this->title = null;
        $this->filename = null;
        // temporary properties
        $this->file = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "Comment on {$this->getSubmission()} by {$this->getUser()}";
    }

    /**
     * Get the comment's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the submission concerned.
     *
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    /**
     * Get the user invited to comment (null when the object is first created).
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user invited to comment.
     *
     * @param User $user The user invited to comment.
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the title of the comment.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title of the comment.
     *
     * @param string|null $title The title of the comment.
     * @return self
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the name of the uploaded file (null when the object is first created).
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Get the uploaded file.
     *
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Set the uploaded file (and the filename at the same time).
     *
     * @param UploadedFile|null $file The submission file.
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
     * Get path to this file in the uploads subdirectory.
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->user ? "comments/user{$this->getUser()->getId()}/{$this->getSubmission()->getId()}/" : null;
    }
}
