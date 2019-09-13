<?php

namespace App\Entity\Candidate;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

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

    public function findFirstYear(): ?int
    {
        return $this->createQueryBuilder('c')
            ->select('MIN(c.start) as minStart')
            ->getQuery()
            ->getSingleScalarResult();
    }

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
