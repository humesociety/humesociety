<?php

namespace App\Entity\Election;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Election\ElectionRepository")
 * @UniqueEntity(
 *     fields="year",
 *     message="There is already an election for this year in the database."
 * )
 *
 * Election objects represent elections for membership of the executive committee. They record the
 * year, the total number of votes, and the population size (i.e. the number of voting members that
 * year). In this way, a permanent record of turnout can be kept in the database.
 *
 * The candidates for each election are those whose term starts that year.
 */
class Election
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $year;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $open;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $votes;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $population;

    // Constructor function; set some default values
    public function __construct()
    {
        $this->year = idate('Y') + 1;
        $this->open = false;
        $this->votes = 0;
        $this->population = 0;
    }

    // ToString function
    public function __toString(): string
    {
        return $this->year;
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpen(): ?bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function getDecade(): ?int
    {
        return $this->year - ($this->year % 10);
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

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

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(int $population): self
    {
        $this->population = $population;

        return $this;
    }

    // Getters for derivative properties
    public function getTurnout(): ?int
    {
        if ($this->population > 0) {
            return round($this->votes / $this->population, 2) * 100;
        }
        return null;
    }
}
