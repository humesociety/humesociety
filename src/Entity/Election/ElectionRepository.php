<?php

namespace App\Entity\Election;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function findAll() : Array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.year', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDecades() : Array
    {
        $decades = $this->createQueryBuilder('e')
            ->select('DISTINCT (e.year - MOD(e.year, 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
    }

    public function findElectionByYear(int $year) : ?Election
    {
        return $this->createQueryBuilder('e')
            ->where('e.year = :year')
            ->setParameter('year', $year)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
