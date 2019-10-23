<?php

namespace App\Entity\EmailTemplate;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An email template.
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"label"},
 *     message="There is already an email template with this label in the database."
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
     * The template's (unique) label, i.e. its identfier for practical purposes.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * The sender of the email (vicepresident|web|conference).
     *
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $sender;

    /**
     * The subject of the email.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * The content of the email.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * The template's group (from `services.yml`).
     *
     * @var string
     */
    private $group;

    /**
     * The title of the email template (from `services.yml`).
     *
     * @var string
     */
    private $title;

    /**
     * The description of the email template (from `services.yml`).
     *
     * @var string
     */
    private $description;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        // persisted properties
        $this->id = null; // Doctrine takes care of this
        $this->label = null;
        $this->sender = null;
        $this->subject = null;
        $this->content = null;
        // fixed properties (defined in services.yml)
        $this->group = null;
        $this->title = null;
        $this->description = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->title ? $this->title : 'uninitialised email template';
    }

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
     * Get the template's (unique) label (null when the object is first created).
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set the template's (unique) label.
     *
     * @param string $label The template's (unique) label.
     * @return self
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

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
     * @param string $sender The sender of the email.
     * @return self
     */
    public function setSender(string $sender): self
    {
        if (in_array($sender, ['vicepresident', 'web', 'conference'])) {
            $this->sender = $sender;
        }
        return $this;
    }

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
     * @param string $subject The subject of the email.
     * @return self
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

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
     * @param string $content The content of the email.
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the template's group (null when the object is first created).
     *
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Set the template's group.
     *
     * @param string $group The template's group.
     * @return self
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get the template's title (null when the object is first created).
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the template's title.
     *
     * @param string $title The template's title.
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the template's description (null when the object is first created).
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the template's description.
     *
     * @param string $description The template's description.
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
