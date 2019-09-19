<?php

namespace App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMembers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMembersInGoodStanding(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.lifetimeMember = true OR u.dues >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMembersInArrears(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.lifetimeMember = false AND u.dues < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMembersExpiringByDate(\DateTime $date): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.lifetimeMember = false AND u.dues = :date')
            ->setParameter('date', $date)
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMembersReceivingHumeStudies(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.receiveHumeStudies = true')
            ->andWhere('u.dues >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findElectableMembers(): QueryBuilder
    {
        // used to generate options in the select dropdown for forms
        // hence returns the query builder, *not* the actual result
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.dues > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('u.lastname, u.firstname', 'ASC');
    }

    public function findReviewVolunteers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.willingToReview = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCommentVolunteers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.willingToComment = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findChairVolunteers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.willingToChair = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
