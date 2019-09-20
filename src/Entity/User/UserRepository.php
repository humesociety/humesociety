<?php

namespace App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The user repository.
 *
 * Controllers should not interact with the user repository directly, but instead use the user
 * handler. The latter injects this class as a dependency, and exposes all the necessary
 * functionality.
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find all users in the database.
     *
     * @return User[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all members in the database.
     *
     * @return User[]
     */
    public function findMembers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all members in good standing in the database.
     *
     * @return User[]
     */
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

    /**
     * Find all members in arrears in the database.
     *
     * @return User[]
     */
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

    /**
     * Find all members members whose membership expires on the given date.
     *
     * @return User[]
     */
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

    /**
     * Find all members who have elected to receive a copy of Hume Studies in the post.
     *
     * @return User[]
     */
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

    /**
     * Find the current EVPT.
     *
     * @return User|null
     */
    public function findVicePresident(): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_EVPT%\'')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find the current technical director.
     *
     * @return User|null
     */
    public function findTechnicalDirector(): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_TECH%\'')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find the current conference organisers.
     *
     * @return User[]
     */
    public function findConferenceOrganisers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_ORGANISER%\'')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the current journal editors.
     *
     * @return User[]
     */
    public function findJournalEditors(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_EDITOR%\'')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get QueryBuilder for all members in good standing.
     *
     * This is used to generate the options in the select dropdown for candidate forms. To this end,
     * we need the QueryBuilder rather than the actual query result.
     *
     * @return QueryBuilder
     */
    public function findElectableMembers(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.dues > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('u.lastname, u.firstname', 'ASC');
    }

    /**
     * Find all users who have indicated a willingness to receive invitations to review.
     *
     * @return User[]
     */
    public function findReviewVolunteers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.willingToReview = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all users who have volunteered to comment on a paper at the next Hume Conference.
     *
     * @return User[]
     */
    public function findCommentVolunteers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.willingToComment = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all users who have volunteered to chair a session at the next Hume Conference.
     *
     * @return User[]
     */
    public function findChairVolunteers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.willingToChair = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
