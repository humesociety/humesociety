<?php

namespace App\Entity\Conference;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ConferenceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.year', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findForthcoming(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.year >= :thisYear')
            ->setParameter('thisYear', idate('Y'))
            ->orderBy('c.year', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findDecades(): array
    {
        $decades = $this->createQueryBuilder('c')
            ->select('DISTINCT (c.year - MOD(c.year, 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
    }

    public function findLatestNumber(): ?int
    {
        return $this->createQueryBuilder('c')
            ->select('MAX(c.number)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLatestYear(): ?int
    {
        return $this->createQueryBuilder('c')
            ->select('MAX(c.year)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
