<?php

namespace App\Entity\Chair;

use App\Entity\Invitation\Invitation;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A session chair for a paper at the Hume Conference (or an invitation to chair).
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"submission", "user"},
 *     errorPath="user",
 *     message="This person has already been invited to chair the discussion of this paper."
 * )
 */
class Chair extends Invitation
{
    /**
     * The chair's unique identifier in the database.
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
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Submission\Submission",
     *     inversedBy="chairs",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $submission;

    /**
     * The user invited to chair.
     *
     * @var User
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\User",
     *     inversedBy="chairs",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "Chair for {$this->getSubmission()} by {$this->getUser()}";
    }

    /**
     * Get the chair's unique identifier (null when the object is first created).
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
     * Get the user invited to chair (null when the object is first created).
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user invited to chair.
     *
     * @param User $user The user invited to chair.
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
