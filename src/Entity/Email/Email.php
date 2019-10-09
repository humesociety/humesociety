<?php

namespace App\Entity\Email;

use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * An email. Emails are created on the fly and not persisted to the database.
 */
class Email
{
    /**
     * The sender of the email.
     *
     * @var string
     */
    private $sender;

    /**
     * The recipient's name'.
     *
     * @var string
     */
    private $recipientName;

    /**
     * The recipient's email address.
     *
     * @var string
     */
    private $recipientEmail;

    /**
     * The subject of the email.
     *
     * @var string
     */
    private $subject;

    /**
     * An attached file.
     *
     * @var UploadedFile|null
     */
    private $attachment;

    /**
     * Associative array of twig variables for use when rendering the email (includes email content).
     *
     * @var Object
     */
    private $twigs;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        $this->twigs = ['content' => null];
        $this->template = 'base';
    }

    /**
     * The twig template to use when rendering the email.
     *
     * Note that this is not the same as the email template entity from which the email was (possibly)
     * created; email template entities contain base content and other properties (sender, subject, etc.)
     * that are independent of the twig template used to render the content of the email itself.
     *
     * @var string
     */
    private $template;

    /**
     * Get the sender of the email (null when the object is created).
     *
     * @return string|null
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * Set the sender of the email.
     *
     * @param string The sender of the email.
     * @return self
     */
    public function setSender(string $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Get the recipient's name (null when the object is created).
     *
     * @return string|null
     */
    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    /**
     * Set the recipient's name.
     *
     * @param string The recipient's name.
     * @return self
     */
    public function setRecipientName($recipientName): self
    {
        $this->recipientName = $recipientName;
        return $this;
    }

    /**
     * Get the recipient's email address (null when the object is created).
     *
     * @return string|null
     */
    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    /**
     * Set the recipient's email address.
     *
     * @param string The recipient's email address.
     * @return self
     */
    public function setRecipientEmail($recipientEmail): self
    {
        $this->recipientEmail = $recipientEmail;
        return $this;
    }

    /**
     * Set the recipient's name and email address simultaneously.
     *
     * @param User|Reviewer The recipient.
     * @return self
     */
    public function setRecipient($recipient): self
    {
        $this->recipientName = $recipient->getFullname();
        $this->recipientEmail = $recipient->getEmail();
        return $this;
    }

    /**
     * Get the subject of the email (null when the object is created).
     *
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Set the subject of the email.
     *
     * @param string The subject of the email.
     * @return self
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get the attached file.
     *
     * @return UploadedFile|null
     */
    public function getAttachment(): ?UploadedFile
    {
        return $this->attachment;
    }

    /**
     * Set the attached file.
     *
     * @param UploadedFile|null The attached file.
     * @return self
     */
    public function setAttachment(?UploadedFile $attachment): self
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * Get the twig variables.
     *
     * @return Object
     */
    public function getTwigs(): array
    {
        return $this->twigs;
    }

    /**
     * Add a twig variable.
     *
     * @param string The name of the variable.
     * @param mixed The value of the variable.
     * @return void
     */
    public function addTwig(string $name, $value): self
    {
        $this->twigs[$name] = $value;
        return $this;
    }

    /**
     * Get the content of the email (null when the object is created).
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->twigs['content'];
    }

    /**
     * Set the content of the email.
     *
     * @param string The content of the email.
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->twigs['content'] = $content;
        return $this;
    }

    /**
     * Get the twig template to use when rendering the email.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Set the twig template to use when rendering the email.
     *
     * @param string The twig template to use when rendering the email.
     * @return self
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }
}
