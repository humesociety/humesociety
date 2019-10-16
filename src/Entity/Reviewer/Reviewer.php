<?php

namespace App\Entity\Reviewer;

use App\Entity\Review\Review;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * A reviewer for the Hume Conference.
 *
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"user"},
 *     message="This user is already registered as a reviewer."
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="There is already a reviewer with this email address."
 * )
 */
class Reviewer
{
    /**
     * The reviewer's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The linked user.
     *
     * @var User|null
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\User\User",
     *     inversedBy="reviewer",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * The reviewer's reviews.
     *
     * @var Review[]
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Review\Review",
     *     mappedBy="reviewer",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $reviews;

    /**
     * The reviewer's email address.
     *
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * The reviewer's first name.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * The reviewer's last name.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * The reviewer's keywords.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $keywords;

    /**
     * The reviewer's secret (randomly generated string for linking to their reviews).
     *
     * @var string
     * @ORM\Column(type="string", length=8)
     */
    private $secret;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        $this->id = null; // Doctrine takes care of this
        $this->user = null;
        $this->reviews = new ArrayCollection();
        $this->email = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->keywords = null;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $this->secret = '';
        for ($i = 0; $i < 8; $i++) {
            $this->secret .= $characters[rand(0, strlen($characters) - 1)];
        }
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->user) {
            return (string) $user;
        }
        return $this->firstname && $this->lastname && $this->email
            ? "{$this->firstname} {$this->lastname} ({$this->email})"
            : 'uninitialised reviewer';
    }

    /**
     * Get the unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
    * Get the linked user.
    *
    * @return User|null
    */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the linked user.
     *
     * @var User|null The linked user.
     * @return self
     */
    public function setUser(?User $user): self
    {
        // remove reviewer association of the current linked user
        if ($this->user) {
            $this->user->setReviewer(null);
        }
        // add reviewer association of the new linked user
        if ($user) {
            $user->setReviewer($this);
        }
        // set the linked user
        $this->user = $user;
        return $this;
    }

    /**
     * Get the reviewer's reviews.
     *
     * @return Reviews[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     * Get the reviewer's accepted reviews.
     *
     * @return Reviews[]
     */
    public function getAcceptedReviews(): Collection
    {
        return $this->reviews->filter(function ($review) {
            return $review->isAccepted();
        });
    }

    /**
     * Get the reviewer's email address (null when the object is first created).
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        if ($this->user && $this->user->getEmail()) {
            $this->user->getEmail();
        }
        return $this->email;
    }

    /**
     * Set the reviewer's email address.
     *
     * @param string The reviewer's email address.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the reviewer's first name (null when the object is first created).
     *
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        if ($this->user && $this->user->getFirstname()) {
            $this->user->getFirstname();
        }
        return $this->firstname;
    }

    /**
     * Set the reviewer's first name.
     *
     * @param string The reviewer's first name.
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * Get the reviewer's last name (null when the object is first created).
     *
     * @return string|null
     */
    public function getLastname(): ?string
    {
        if ($this->user && $this->user->getLastname()) {
            $this->user->getLastname();
        }
        return $this->lastname;
    }

    /**
     * Set the reviewer's last name.
     *
     * @param string The reviewer's last name.
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get the reviewer's full name (null when the object is first created).
     *
     * @return string|null
     */
    public function getFullname(): ?string
    {
        return "{$this->getFirstname()} {$this->getLastname()}";
    }

    /**
     * Get the reviewer's keywords.
     *
     * @return string|null
     */
    public function getKeywords(): ?string
    {
        if ($this->user && $this->user->getKeywords()) {
            $this->user->getKeywords();
        }
        return $this->keywords;
    }

    /**
     * Set the reviewer's keywords.
     *
     * @param string|null The reviewer's keywords.
     * @return self
     */
    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * Get the reviewer's secret.
     *
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Get whether the reviewer has explicitly indicated a willingness to review.
     *
     * @return bool
     */
    public function isWillingToReview(): bool
    {
        return $this->user ? $this->user->isWillingToReview() : false;
    }

    /**
     * Set user-related properties based on those of the linked user.
     *
     * @return void
     */
    public function setPropertiesFromUser()
    {
        if ($this->user) {
            $this->email = $this->user->getEmail();
            $this->firstname = $this->user->getFirstname();
            $this->lastname = $this->user->getLastname();
            if ($this->user->getKeywords()) {
                $this->keywords = $this->user->getKeywords();
            }
        }
    }
}
