<?php

namespace App\Entity\Paper;

use App\Entity\Conference\Conference;
use App\Entity\Invitation\Invitation;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An invited paper for the Hume Conference.
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"conference", "user"},
 *     errorPath="user",
 *     message="This person has already been invited to present a paper at this conference."
 * )
 */
class Paper extends Invitation
{
    /**
     * The paper's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The conference the paper is for.
     *
     * @var Conference
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Conference\Conference",
     *     inversedBy="papers",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $conference;

    /**
     * The user invited to speak.
     *
     * @var User
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\User",
     *     inversedBy="papers",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * The title of the paper.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * The abstract of the paper.
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $abstract;

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
     *     mimeTypesMessage = "Please upload your paper in Word or RTF format."
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
     * The path to this paper's file in the uploads directory.
     * @var string|null
     */
    private $path;

    /**
     * Constructor function.
     *
     * @param Conference $conference The conference the paper for.
     * @throws \Exception
     * @return void
     */
    public function __construct(Conference $conference)
    {
        // invitation properties
        parent::__construct();
        // persisted properties
        $this->id = null; // doctrine will take care of this
        $this->conference = $conference;
        $this->user = null;
        $this->title = null;
        $this->abstract = null;
        $this->filename = null;
        // temporary properties
        $this->file = null;
        // derivative properties
        $this->path = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->title) {
            return "“{$this->title}”";
        }
        if ($this->user) {
            return "Paper by {$this->user->getFullname()}";
        }
        return 'uninitialised paper';
    }

    /**
     * Get the paper's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the conference this paper is for.
     *
     * @return Conference
     */
    public function getConference(): Conference
    {
        return $this->conference;
    }

    /**
     * Get the user invited to speak (null when the object is first created).
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user invited to speak.
     *
     * @param User $user The user invited to speak.
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
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
     * @param UploadedFile|null $file The uploaded file.
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
     * Get path to the file in the uploads subdirectory.
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->user ? "papers/user{$this->user->getId()}/{$this->getConference()->getNumber()}/" : null;
    }
}
