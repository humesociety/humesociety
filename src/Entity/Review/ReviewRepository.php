<?php

namespace App\Entity\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The review repository.
 *
 * Controllers should not interact with the review repository directly, but instead use the review
 * handler. The latter injects this class as a dependency, and exposes all the necessary
 * functionality.
 */
class ReviewRepository extends ServiceEntityRepository
{
   /**
    * Constructor function.
    *
    * @return void
    */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Review::class);
    }
}
