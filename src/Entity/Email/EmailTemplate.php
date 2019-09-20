<?php

namespace App\Entity\Email;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An email template.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Email\EmailTemplateRepository")
 * @UniqueEntity(
 *     fields={"type"},
 *     message="There is already an email template with this type."
 * )
 */
class EmailTemplate
{
    /**
     * The template's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Get the template's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The template's (unique) type, i.e. its identfier for practical purposes.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * Get the template's (unique) type (null when the object is first created).
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set the template's (unique) type.
     *
     * @param string The template's (unique) type.
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * The sender of the email: 'vicepresident', 'web', or 'conference'.
     *
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $sender;

    /**
     * Get the sender of the email (null when the object is first created).
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
        if (in_array($sender, ['president', 'vicepresident', 'web', 'conference'])) {
            $this->sender = $sender;
        }

        return $this;
    }

    /**
     * The subject of the email.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * Get the subject of the email (null when the object is first created).
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
     * The content of the email.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * Get the content of the email (null when the object is first created).
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the content of the email.
     *
     * @param string The content of the email.
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): ?string
    {
        return $this->type;
    }
}
