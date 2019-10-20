<?php

namespace App\Entity\Invitation;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An invitation is an abstract superclass inherited by chairs, comments, reviews, and papers - which
 * are invitations to chair, comment, etc. and consequently have several fields and methods in common.
 *
 * @ORM\MappedSuperclass()
 */
class Invitation
{
    /**
     * The date the invitation was sent.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="date")
     */
    private $dateInvitationSent;

    /**
     * The invitation's secret (a randomly generated string for creating a link for replying).
     *
     * @var string
     * @ORM\Column(type="string", length=16)
     */
    private $secret;

    /**
     * Whether the invitation is accepted (null means decision pending; false means rejected).
     *
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accepted;

    /**
     * How many invitation reminder emails have been sent.
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $invitationReminderEmails;

    /**
     * The date on which the last invitation reminder email was sent.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateLastInvitationReminderSent;

    /**
     * How many submission reminder emails have been sent.
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $submissionReminderEmails;

    /**
     * The date on which the last submission reminder email was sent.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateLastSubmissionReminderSent;

    /**
     * When whatever was invited was submitted.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateSubmitted;

    /**
     * The user's firstname (used when inviting a new user).
     *
     * @var string|null
     * @Assert\NotBlank(groups={"new"}, message="Please enter a firstname.")
     */
    private $firstname;

    /**
     * The user's lastname (used when inviting a new user).
     *
     * @var string|null
     * @Assert\NotBlank(groups={"new"}, message="Please enter a lastname.")
     */
    private $lastname;

    /**
     * The user's email (used when inviting a new user).
     *
     * @var string|null
     * @Assert\NotBlank(groups={"new"}, message="Please enter an email.")
     */
    private $email;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        $this->dateInvitationSent = new \DateTime('today');
        $this->secret = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 16; $i++) {
            $this->secret .= $characters[rand(0, strlen($characters) - 1)];
        }
        $this->accepted = null;
        $this->invitationReminderEmails = 0;
        $this->dateLastInvitationReminderSent = null;
        $this->submissionReminderEmails = 0;
        $this->dateLastSubmissionReminderSent = null;
        $this->dateSubmitted = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->email = null;
    }

    /**
     * Get the date the invitation was sent.
     *
     * @return \DateTimeInterface
     */
    public function getDateInvitationSent(): \DateTimeInterface
    {
        return $this->dateInvitationSent;
    }

    /**
     * Get the invitation's secret.
     *
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Get whether the invitation is accepted.
     *
     * @return bool|null
     */
    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    /**
     * Set whether the invitation is accepted.
     *
     * @param bool|null Whether the submission is accepted.
     * @return self
     */
    public function setAccepted(?bool $accepted): self
    {
        $this->accepted = $accepted;
        return $this;
    }

    /**
     * Get the number of invitation reminder emails sent.
     *
     * @return int
     */
    public function getInvitationReminderEmails(): int
    {
        return $this->invitationReminderEmails;
    }

    /**
     * Increment the number of invitation reminder emails sent.
     *
     * @return self
     */
    public function incrementInvitationReminderEmails(): self
    {
        $this->invitationReminderEmails += 1;
        $this->dateLastInvitationReminderSent = new \DateTime('today');
        return $this;
    }

    /**
     * Get the date the last invitation reminder email was sent.
     *
     * @return \DateTimeInterface|null
     */
    public function getDateLastInvitationReminderSent(): ?\DateTimeInterface
    {
        return $this->dateLastInvitationReminderSent;
    }

    /**
     * Get the number of submission reminder emails sent.
     *
     * @return int
     */
    public function getSubmissionReminderEmails(): int
    {
        return $this->submissionReminderEmails;
    }

    /**
     * Increment the number of submission reminder emails sent.
     *
     * @return self
     */
    public function incrementSubmissionReminderEmails(): self
    {
        $this->submissionReminderEmails += 1;
        $this->lastSubmissionReminderEmail = new \DateTime('today');
        return $this;
    }

    /**
     * Get the date the last invitation reminder email was sent.
     *
     * @return \DateTimeInterface|null
     */
    public function getLastSubmissionReminderEmail(): ?\DateTimeInterface
    {
        return $this->lastSubmissionReminderEmail;
    }

    /**
     * Get when whatever was invited was submitted.
     *
     * @return \DateTimeInterface|null
     */
    public function getDateSubmitted(): ?\DateTimeInterface
    {
        return $this->dateSubmitted;
    }

    /**
     * Set when whatever was invited was submitted.
     *
     * @return self
     */
    public function setDateSubmitted(): self
    {
        $this->dateSubmitted = new \DateTime('today');
        return $this;
    }

    /**
     * Get whether whatever was invited was submitted.
     *
     * @return bool
     */
    public function isSubmitted(): bool
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
     * Get the review's status icon.
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
     * Get the new user's firstname.
     *
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set the new user's firstname.
     *
     * @param string The new user's firstname.
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * Get the new user's lastmame.
     *
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set the new user's lastname.
     *
     * @param string The new user's lastname.
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get the new user's email.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the new user's email.
     *
     * @param string The new user's email.
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
}
