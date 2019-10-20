<?php

namespace App\Entity\DuesPayment;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * A dues payment records a PayPal payment in the database.
 *
 * @ORM\Entity()
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * The date the payment was made.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="date")
     */
    private $date;

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
     * Constructor function
     *
     * @return void
     */
    public function __construct(User $user, string $paypalOrderId)
    {
        $this->id = null; // doctrine takes care of this
        $this->paypalOrderId = $paypalOrderId;
        $this->user = $user;
        $this->date = new \DateTime('today');
        $this->amount = null;
        $this->description = null;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->paypalOrderId;
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
     * Get the PayPal order identifier.
     *
     * @return string
     */
    public function getPaypalOrderId(): string
    {
        return $this->paypalOrderId;
    }

    /**
     * Get the user who made the payment.
     *
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
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
}
