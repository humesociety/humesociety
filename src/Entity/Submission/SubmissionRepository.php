<?php

namespace App\Entity\Submission;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The submission repository.
 *
 * Controllers should not interact with the submission repository directly, but instead use the
 * submission handler. The latter injects this class as a dependency, and exposes all the
 * necessary functionality.
 */
class SubmissionRepository extends ServiceEntityRepository
{
   /**
    * Constructor function.
    *
    * @return void
    */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Submission::class);
    }
}
