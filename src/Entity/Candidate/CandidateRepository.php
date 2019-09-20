<?php

namespace App\Entity\Candidate;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The candidate repository.
 *
 * Controllers should not interact with the candidate repository directly, but instead use the
 * candidate handler. The latter injects this class as a dependency, and exposes all the
 * necessary functionality.
 */
class CandidateRepository extends ServiceEntityRepository
{
   /**
    * Constructor function.
    *
    * @return void
    */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    /**
     * Find how many candidates there are in the database.
     *
     * @return int
     */
    private function countCandidates(): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find all EVPTs.
     *
     * @return Candidate[]
     */
    public function findEVPTs(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.elected = TRUE')
            ->andWhere('c.evpt = TRUE')
            ->orderBy('c.start', 'DESC')
            ->addOrderBy('c.lastname, c.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all committee members.
     *
     * @return Candidate[]
     */
    public function findExecs(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.elected = TRUE')
            ->andWhere('c.evpt = FALSE')
            ->orderBy('c.start', 'DESC')
            ->addOrderBy('c.lastname, c.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all start years.
     *
     * @return int[]
     */
    public function findYears(): array
    {
        if ($this->countCandidates() == 0) {
            return [];
        }

        $years = $this->createQueryBuilder('c')
            ->select('DISTINCT c.start')
            ->orderBy('c.start')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $years);
    }

    /**
     * Find all candidates for a given start year.
     *
     * @param int The start year.
     * @return Candidate[]
     */
    public function findCandidatesByYear(int $year): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.start = :year')
            ->setParameter('year', $year)
            ->orderBy('c.lastname, c.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
