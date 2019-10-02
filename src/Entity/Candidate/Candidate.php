<?php

namespace App\Entity\Candidate;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * A candidate for election to the executive committee. If marked as `elected`, a member of the
 * executive committee.
 *
 * Candidates can optionally be linked to a user in the database. If so, the `firstname`,
 * `lastname`, and `institution` fields here potentially duplicate information in the Users table.
 * That information may change or be deleted, however, when the information here should stay the
 * same (e.g. if the person moves institution, or deletes their account.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Candidate\CandidateRepository")
 */
class Candidate
{
    /**
     * The candidate's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The candidate's firstname.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * The candidate's lastname.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * The candidate's institution.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $institution;

    /**
     * The associated user.
     *
     * This field is nullable in case the person subsequently deletes their account, and because
     * our records predate this web site.
     *
     * @var User|null
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="candidacies", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * The start year of this term of office.
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $start;

    /**
     * The end year of this term of office.
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $end;

    /**
     * A description of the candidate (for voters to read).
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * How many votes the candidate has received.
     *
     * @var int
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $votes;

    /**
     * Whether the candidate is elected.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $elected;

    /**
     * Whether the candidate is reelectable after this term of office.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $reelectable;

    /**
     * Whether the candidate is standing for president.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $president;

    /**
     * Whether the candidate is standing for EVPT.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $evpt;

    /**
     * Constructor function.
     *
     * @return void
     */
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

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->firstname.' '.$this->lastname.' ('.$this->start.' - '.$this->end.')';
    }

    /**
     * Get the candidate's unique identifier (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the candidate's firstname (null when the object is first created).
     *
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set the candidate's firstname.
     *
     * @param string The candidate's firstname.
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the candidate's lastname (null when the object is first created).
     *
     * @return int|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set the candidate's lastname.
     *
     * @param string The candidate's lastname.
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the candidate's institution.
     *
     * @return string|null
     */
    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    /**
     * Set the candidate's institution.
     *
     * @param string|null The candidate's institution.
     * @return self
     */
    public function setInstitution(string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get the associated user.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the associated user.
     *
     * @param User|null The associated user.
     * @return self
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the start year of this term of office (null when the object is first created).
     *
     * @return int|null
     */
    public function getStart(): ?int
    {
        return $this->start;
    }

    /**
     * Set the start year of this term of office.
     *
     * @param int The start year of this term of office.
     */
    public function setStart(int $start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get the end year of this term of office (null when the object is first created).
     *
     * @return int|null
     */
    public function getEnd(): ?int
    {
        return $this->end;
    }

    /**
     * Set the end year of this term of office.
     *
     * @param int The end year of this term of office.
     */
    public function setEnd(int $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get the description of this candidate.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the description of this candidate.
     *
     * @param string|null The description of this candidate.
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get how many votes the candidate has received.
     *
     * @return int
     */
    public function getVotes(): ?int
    {
        return $this->votes;
    }

    /**
     * Set how many votes the candidate has received.
     *
     * @param int How many votes the candidate has received.
     * @return self
     */
    public function setVotes(int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    /**
     * Get whether the candidate is elected.
     *
     * @return bool
     */
    public function getElected(): bool
    {
        return $this->elected;
    }

    /**
     * Set whether the candidate is elected.
     *
     * @param bool Whether the candidate is elected.
     * @return self
     */
    public function setElected(bool $elected): self
    {
        $this->elected = $elected;

        return $this;
    }

    /**
     * Get whether the candidate is reelectable after this term of office.
     *
     * @return bool
     */
    public function getReelectable(): ?bool
    {
        return $this->reelectable;
    }

    /**
     * Set whether the candidate is reelectable after this term of office.
     *
     * @param bool Whether the candidate is reelectable after this term of office.
     * @return self
     */
    public function setReelectable(bool $reelectable): self
    {
        $this->reelectable = $reelectable;

        return $this;
    }

    /**
     * Get whether the candidate is standing for president.
     *
     * @return bool
     */
    public function getPresident(): ?bool
    {
        return $this->president;
    }

    /**
     * Set whether the candidate is standing for president.
     *
     * @param bool Whether the candidate is standing for president.
     * @return self
     */
    public function setPresident(bool $president): self
    {
        $this->president = $president;

        return $this;
    }

    /**
     * Get whether the candidate is standing for EVPT.
     *
     * @return bool
     */
    public function getEvpt(): ?bool
    {
        return $this->evpt;
    }

    /**
     * Set whether the candidate is standing for EVPT.
     *
     * @param bool Whether the candidate is standing for EVPT.
     * @return self
     */
    public function setEvpt(bool $evpt): self
    {
        $this->evpt = $evpt;

        return $this;
    }
}
