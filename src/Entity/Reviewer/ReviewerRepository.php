<?php

namespace App\Entity\Reviewer;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The reviewer repository.
 *
 * Controllers should not interact with the reviewer repository directly, but instead use the
 * reviewer handler. The latter injects this class as a dependency, and exposes all the
 * necessary functionality.
 */
class ReviewerRepository extends ServiceEntityRepository
{
   /**
    * Constructor function.
    *
    * @return void
    */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Reviewer::class);
    }

    /**
     * Find all reviewers.
     *
     * @return Reviewers[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.lastname, r.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
