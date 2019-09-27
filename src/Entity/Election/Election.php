<?php

namespace App\Entity\Election;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Election objects represent elections for membership of the executive committee. They record the
 * year, the total number of votes, and the population size (i.e. the number of voting members that
 * year). In this way, a permanent record of turnout can be kept in the database.
 *
 * The candidates for each election are those whose term starts that year.
 *
 * @ORM\Entity(repositoryClass="App\Entity\Election\ElectionRepository")
 * @UniqueEntity(
 *     fields="year",
 *     message="There is already an election for this year in the database."
 * )
 */
class Election
{
    /**
     * The election's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The year for which the election is held (typically the year after the election happens).
     *
     * @var int
     * @ORM\Column(type="integer", unique=true)
     */
    private $year;

    /**
     * Whether the election is currently open.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $open;

    /**
     * How many members have voted.
     *
     * @var int
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $votes;

    /**
     * The number of members when the election is held.
     *
     * @var int
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $population;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        $this->year = idate('Y') + 1;
        $this->open = false;
        $this->votes = 0;
        $this->population = 0;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->year;
    }

    /**
     * Get the unique identifier for this election (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get whether the election is open.
     *
     * @return bool
     */
    public function getOpen(): bool
    {
        return $this->open;
    }

    /**
     * Set whether the election is open.
     *
     * @param bool Whether the election is open.
     * @return self
     */
    public function setOpen(bool $open): self
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get the year for which the election is held (null when the object is first created).
     *
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * Set the year for which the election is held.
     *
     * @param int The year for which the election is held.
     * @return self
     */
    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the decade for which the election is held (null when the object is first created).
     *
     * @return int|null
     */
    public function getDecade(): ?int
    {
        return $this->year - ($this->year % 10);
    }

    /**
     * Get the number of votes cast.
     *
     * @return int
     */
    public function getVotes(): int
    {
        return $this->votes;
    }

    /**
     * Set the number of votes cast.
     *
     * @param int The number of votes cast.
     * @return self
     */
    public function setVotes(int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    /**
     * Get the population.
     *
     * @return int
     */
    public function getPopulation(): int
    {
        return $this->population;
    }

    /**
     * Set the population.
     *
     * @param int The population.
     * @return self
     */
    public function setPopulation(int $population): self
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get the turnout for the election as a percentage.
     *
     * @return int|null
     */
    public function getTurnout(): ?int
    {
        if ($this->population > 0) {
            return round($this->votes / $this->population, 2) * 100;
        }

        return null;
    }
}
