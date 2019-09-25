<?php

namespace App\Entity\DuesPayment;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The dues pyament repository.
 *
 * Controllers should not interact with the dues payment repository directly, but instead use the
 * dues payment handler. The latter injects this class as a dependency, and exposes all the
 * necessary functionality.
 */
class DuesPaymentRepository extends ServiceEntityRepository
{
    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DuesPayment::class);
    }

    /**
     * Find all dues payments in the database.
     *
     * @return DuesPayment[]
     */
    public function findAll() : Array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
