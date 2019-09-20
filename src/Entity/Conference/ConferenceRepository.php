<?php

namespace App\Entity\Conference;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The conference repository.
 *
 * Controllers should not interact with the conference repository directly, but instead use the
 * conference handler. The latter injects this class as a dependency, and exposes all the
 * necessary functionality.
 */
class ConferenceRepository extends ServiceEntityRepository
{
    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    /**
     * Find how many conferences there are in the database.
     *
     * @return int
     */
    private function countConferences(): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find all conferences in the database.
     *
     * @return Conference[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.year', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all conferences for the current year or later.
     *
     * @return Conference[]
     */
    public function findConferencesForThisYearAndLater(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.year >= :thisYear')
            ->setParameter('thisYear', idate('Y'))
            ->orderBy('c.year', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all decades of conferences in the database.
     *
     * @return int[]
     */
    public function findDecades(): array
    {
        if ($this->countConferences() == 0) {
            return [];
        }

        $decades = $this->createQueryBuilder('c')
            ->select('DISTINCT (c.year - MOD(c.year, 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
    }

    /**
     * Find the number of the latest conference in the database (0 if there aren't any).
     *
     * @return int
     */
    public function findLatestNumber(): int
    {
        if ($this->countConferences() == 0) {
            return 0;
        }

        return $this->createQueryBuilder('c')
            ->select('MAX(c.number)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find the year of the latest conference in the database (this year if there aren't any).
     *
     * @return int
     */
    public function findLatestYear(): int
    {
        if ($this->countConferences() == 0) {
            return date('Y');
        }

        return $this->createQueryBuilder('c')
            ->select('MAX(c.year)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
