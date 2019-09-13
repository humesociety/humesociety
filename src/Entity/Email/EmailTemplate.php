<?php

namespace App\Entity\Email;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Email\EmailTemplateRepository")
 */
class EmailTemplate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $senderName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $senderEmailAddress;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    // Constructor function
    // not needed

    // toString function
    public function __toString(): ?string
    {
        return $this->type;
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSenderEmailAddress(): ?string
    {
        return $this->senderEmailAddress;
    }

    public function setSenderEmailAddress(string $senderEmailAddress): self
    {
        $this->senderEmailAddress = $senderEmailAddress;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
