<?php

namespace App\Entity\Election;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

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
     * @var EntityRepository
     */
    private $repository;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface $manager The Doctrine entity manager.
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
    public function getElections(): array
    {
        return $this->repository->createQueryBuilder('e')
            ->orderBy('e.year', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the currently open election (if any).
     *
     * @return Election|null
     * @throws NonUniqueResultException
     */
    public function getOpenElection(): ?Election
    {
        return $this->repository->createQueryBuilder('e')
            ->where('e.open = TRUE')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the currently open election run-off (if any).
     *
     * @return Election|null
     * @throws NonUniqueResultException
     */
    public function getOpenElectionRunOff(): ?Election
    {
        return $this->repository->createQueryBuilder('e')
            ->where('e.runOffOpen = TRUE')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the election for the given year.
     *
     * @param int $year The year of the election.
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
    public function getDecades(): array
    {
        $decades = $this->repository->createQueryBuilder('e')
            ->select('DISTINCT (e.year - MOD(e.year, 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
    }

    /**
     * Save/update an election.
     *
     * @param Election $election The election to save/update.
     * @return void
     */
    public function saveElection(Election $election)
    {
        $this->manager->persist($election);
        $this->manager->flush();
    }

    /**
     * Delete an election.
     *
     * @param Election $election The election to delete.
     * @return void
     */
    public function deleteElection(Election $election)
    {
        $this->manager->remove($election);
        $this->manager->flush();
    }
}
