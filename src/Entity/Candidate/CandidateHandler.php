<?php

namespace App\Entity\Candidate;

use Doctrine\ORM\EntityManagerInterface;

/**
 * The candidate handler contains the main business logic for reading and writing candidate data.
 */
class CandidateHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The candidate repository.
     *
     * @var CandidateRepository
     */
    private $repository;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Candidate::class);
    }

    /**
     * Get all EVPTs.
     *
     * @return Candidate[]
     */
    public function getEvpts(): array
    {
        return $this->repository->findEVPTs();
    }

    /**
     * Get all executive committee members.
     *
     * @return Candidate[]
     */
    public function getExecs(): array
    {
        return $this->repository->findExecs();
    }

    /**
     * Get an array of start years.
     *
     * @return int[]
     */
    public function getYears(): array
    {
        return $this->repository->findYears();
    }

    /**
     * Get an array of candidates for a given start year.
     *
     * @param int The start year.
     * @return Candidate[]
     */
    public function getCandidatesByYear(int $year): array
    {
        return $this->repository->findCandidatesByYear($year);
    }

    /**
     * Save/update a candidate in the database.
     *
     * @param Candidate The candidate to save/update.
     */
    public function saveCandidate(Candidate $candidate)
    {
        $this->manager->persist($candidate);
        $this->manager->flush();
    }

    /**
     * Unlink a user from a candidate.
     *
     * @param Candidate The candidate to unlink.
     */
    public function unlinkUser(Candidate $candidate)
    {
        $candidate->setUser(null);
        $this->manager->remove($candidate);
        $this->manager->flush();
    }

    /**
     * Delete a candidate from the database.
     *
     * @param Candidate The candidate to delete.
     */
    public function deleteCandidate(Candidate $candidate)
    {
        $this->manager->remove($candidate);
        $this->manager->flush();
    }

    /**
     * Elect a candidate.
     *
     * @param Candidate The candidate to elect.
     */
    public function electCandidate(Candidate $candidate)
    {
        $candidate->setElected(true);
        $this->manager->persist($candidate);
        $this->manager->flush();
    }
}
