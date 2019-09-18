<?php

namespace App\Entity\Candidate;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Candidate\CandidateRepository")
 *
 * Candidate objects represent candidates for election to the executive committee (including the
 * Executive Vice President-Treasurer). If marked as 'elected', they also represent members of the
 * executive committee. They can optionally be linked to a User in the database.
 *
 * If linked to a User, the `firstname`, `lastname`, and `institution` fields here potentially
 * duplicate information in the Users table. That information may change or be deleted, however,
 * when the information here should stay the same (e.g. if the person moves institution, or leaves
 * the society, deleting their account altogether).
 */
class Candidate
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
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $institution;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $start;

    /**
     * @ORM\Column(type="integer")
     */
    private $end;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $votes;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $elected;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $reelectable;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $president;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $evpt;

    // Constructor function; set some default values
    public function __construct()
    {
        $this->start = idate('Y') + 1; // default should be candidates for next year's term
        $this->end = $this->start + 2; // terms last three years by default
        $this->votes = 0;
        $this->elected = false;
        $this->reelectable = true;
        $this->president = false;
        $this->evpt = false;
    }

    // to string
    public function __toString()
    {
        return $this->getLastname().', '.$this->getFirstname();
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function setStart(int $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function setEnd(int $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVotes(): ?int
    {
        return $this->votes;
    }

    public function setVotes(int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    public function getElected(): ?bool
    {
        return $this->elected;
    }

    public function setElected(bool $elected): self
    {
        $this->elected = $elected;

        return $this;
    }

    public function getReelectable(): ?bool
    {
        return $this->reelectable;
    }

    public function setReelectable(bool $reelectable): self
    {
        $this->reelectable = $reelectable;

        return $this;
    }

    public function getPresident(): ?bool
    {
        return $this->president;
    }

    public function setPresident(bool $president): self
    {
        $this->president = $president;

        return $this;
    }

    public function getEvpt(): ?bool
    {
        return $this->evpt;
    }

    public function setEvpt(bool $evpt): self
    {
        $this->evpt = $evpt;

        return $this;
    }
}
