<?php

namespace App\Entity\Text;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The text repository.
 *
 * Controllers should not interact with the text repository directly, but instead use the text
 * handler. The latter injects this class as a dependency, and exposes all the necessary
 * functionality.
 */
class TextRepository extends ServiceEntityRepository
{
   /**
    * Constructor function.
    *
    * @return void
    */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Text::class);
    }
}
