<?php

namespace App\Entity\Email;

/**
 * An email. Emails are created on the fly and not persisted to the database.
 */
class Email extends \Swift_Message
{
    /**
     * The sender of the email.
     *
     * @var string
     */
    private $sender;

    /**
     * The recipient of the email.
     *
     * @var User|Reviewer
     */
    private $recipient;

    /**
     * The content of the email.
     *
     * @var string
     */
    private $content;

    /**
     * Get the sender of the email (null when the object is created).
     *
     * @return string|null
     */
    public function getSender(): string
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
        $this->setFrom
    }
}
