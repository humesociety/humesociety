<?php

namespace App\Entity\EmailTemplate;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The email template repository.
 *
 * Controllers should not interact with the email template repository directly, but instead use the
 * email template handler. The latter injects this class as a dependency, and exposes all the
 * necessary functionality.
 */
class EmailTemplateRepository extends ServiceEntityRepository
{
    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EmailTemplate::class);
    }
}
