<?php

namespace App\Entity\DuesPayment;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DuesPaymentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DuesPayment::class);
    }

    public function findAll() : Array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
