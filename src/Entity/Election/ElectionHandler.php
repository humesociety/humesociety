<?php

namespace App\Entity\Election;

use Doctrine\ORM\EntityManagerInterface;

/**
 * The election handler contains the main business logic for reading and writing election data.
 */
class ElectionHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The election repository.
     *
     * @var ElectionRepository
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
        $this->repository = $manager->getRepository(Election::class);
    }

    /**
     * Get all elections.
     *
     * @return Election[]
     */
    public function getElections(): Array
    {
        return $this->repository->findAll();
    }

    /**
     * Get the election for the given year.
     *
     * @return Election|null
     */
    public function getElectionByYear(int $year): ?Election
    {
        return $this->repository->findOneByYear($year);
    }

    /**
     * Get the decades of all elections in the database.
     *
     * @return int[]
     */
    public function getDecades(): Array
    {
        return $this->repository->findDecades();
    }

    /**
     * Save an election to the database.
     *
     * @return void
     */
    public function saveElection(Election $election)
    {
        $this->manager->persist($election);
        $this->manager->flush();
    }

    /**
     * Delete an election from the database.
     *
     * @return void
     */
    public function deleteElection(Election $election)
    {
        $this->manager->remove($election);
        $this->manager->flush();
    }
}
