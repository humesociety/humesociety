<?php

namespace App\Entity\Candidate;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

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
        $this->repository = $manager->getRepository(Candidate::class);
    }

    /**
     * Find how many candidates there are in the database.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return int
     */
    private function countCandidates(): int
    {
        return $this->repository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get all EVPTs.
     *
     * @return Candidate[]
     */
    public function getEvpts(): array
    {
        return $this->repository->createQueryBuilder('c')
            ->where('c.elected = TRUE')
            ->andWhere('c.evpt = TRUE')
            ->orderBy('c.start', 'DESC')
            ->addOrderBy('c.lastname, c.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all executive committee members.
     *
     * @return Candidate[]
     */
    public function getExecs(): array
    {
        return $this->repository->createQueryBuilder('c')
            ->where('c.elected = TRUE')
            ->andWhere('c.evpt = FALSE')
            ->orderBy('c.start', 'DESC')
            ->addOrderBy('c.lastname, c.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an array of start years.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return int[]
     */
    public function getYears(): array
    {
        if ($this->countCandidates() === 0) {
            return [];
        }
        $years = $this->repository->createQueryBuilder('c')
            ->select('DISTINCT c.start')
            ->orderBy('c.start')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $years);
    }

    /**
     * Get an array of candidates for a given start year.
     *
     * @param int $year The start year.
     * @return Candidate[]
     */
    public function getCandidatesByYear(int $year): array
    {
        return $this->repository->createQueryBuilder('c')
            ->where('c.start = :year')
            ->setParameter('year', $year)
            ->orderBy('c.lastname, c.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an array of run-off andidates for a given start year.
     *
     * @param int $year The start year.
     * @return Candidate[]
     */
    public function getRunOffCandidatesByYear(int $year): array
    {
        return $this->repository->createQueryBuilder('c')
            ->where('c.inRunOff = TRUE')
            ->andWhere('c.start = :year')
            ->setParameter('year', $year)
            ->orderBy('c.lastname, c.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Save/update a candidate in the database.
     *
     * @param Candidate $candidate The candidate to save/update.
     */
    public function saveCandidate(Candidate $candidate)
    {
        $this->manager->persist($candidate);
        $this->manager->flush();
    }

    /**
     * Delete a candidate from the database.
     *
     * @param Candidate $candidate The candidate to delete.
     */
    public function deleteCandidate(Candidate $candidate)
    {
        $this->manager->remove($candidate);
        $this->manager->flush();
    }
}
