<?php

namespace App\Entity\Issue;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IssueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Issue::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.volume', 'ASC')
            ->addOrderBy('i.number', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllReversed(): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.volume', 'DESC')
            ->addOrderBy('i.number', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByVolumeAndNumber($volume, $number): ?Issue
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.volume = :volume')
            ->andWhere('i.number = :number')
            ->setParameter('volume', $volume)
            ->setParameter('number', $number)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestIssue(): ?Issue
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.volume', 'DESC')
            ->addOrderBy('i.number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findDecades(): array
    {
        $decades = $this->createQueryBuilder('i')
            ->select('DISTINCT ((i.volume + 1974) - MOD((i.volume + 1974), 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
    }
}
