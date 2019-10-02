<?php

namespace App\Entity\Election;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The election repository.
 *
 * Controllers should not interact with the election repository directly, but instead use the
 * election handler. The latter injects this class as a dependency, and exposes all the
 * necessary functionality.
 */
class ElectionRepository extends ServiceEntityRepository
{
    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Election::class);
    }

    /**
     * Find all elections.
     *
     * @return Election[]
     */
    public function findAll(): Array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.year', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the decades of all elections.
     *
     * @return int[]
     */
    public function findDecades(): Array
    {
        $decades = $this->createQueryBuilder('e')
            ->select('DISTINCT (e.year - MOD(e.year, 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
    }
}
