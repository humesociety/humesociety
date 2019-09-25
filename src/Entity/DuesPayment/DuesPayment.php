<?php

namespace App\Entity\DuesPayment;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * A dues payment records a PayPal payment in the database.
 *
 * @ORM\Entity(repositoryClass="App\Entity\DuesPayment\DuesPaymentRepository")
 */
class DuesPayment
{
    /**
     * The payment's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The PayPal order identifier.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $paypalOrderId;

    /**
     * The user who made the payment.
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * The amount paid.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $amount;

    /**
     * The description associated with the payment.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * The date the payment was made.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * Constructor function
     *
     * @return void
     */
    public function __construct()
    {
        $this->date = new \DateTime();
    }

    /**
     * Get the payment's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the PayPal order ID (null when the object is first created).
     *
     * @return string|null
     */
    public function getPaypalOrderId(): ?string
    {
        return $this->paypalOrderId;
    }

    /**
     * Set the PayPal order ID.
     *
     * @param string The PayPal order ID.
     * @return self
     */
    public function setPaypalOrderId(string $paypalOrderId): self
    {
        $this->paypalOrderId = $paypalOrderId;

        return $this;
    }

    /**
     * Get the user who made the payment (null when the object is first created).
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user who made the payment.
     *
     * @param User The user who made the payment.
     * @return self
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the amount paid (null when the object is first created).
     *
     * @return string|null
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * Set the amount paid.
     *
     * @param string The amount paid.
     * @return self
     */
    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the description of the payment (null when the object is first created).
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the description of the payment.
     *
     * @param string The description of the payment.
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the date of the payment.
     *
     * @return \DateTimeInterface The date of the payment.
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
}
