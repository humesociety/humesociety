<?php

namespace App\Entity\User;

use App\Entity\DuesPayment\DuesPayment;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The user handler contains the main business logic for reading and writing user data.
 */
class UserHandler
{
    /**
     * The Doctrine entity manager (dependency injection).
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The user repository (dependency injection).
     *
     * @var ConferenceRepository
     */
    private $repository;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(User::class);
    }

    /**
     * Get an array of all users.
     *
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get an array of all members.
     *
     * @return User[]
     */
    public function getMembers(): array
    {
        return $this->repository->findMembers();
    }

    /**
     * Get an array of all members in good standing.
     *
     * @return User[]
     */
    public function getMembersInGoodStanding(): array
    {
        return $this->repository->findMembersInGoodStanding();
    }

    /**
     * Get an array of all members in arrears.
     *
     * @return User[]
     */
    public function getMembersInArrears(): array
    {
        return $this->repository->findMembersInArrears();
    }

    /**
     * Get an array of all members whose membership expires (at the end of) this month.
     *
     * @return User[]
     */
    public function getMembersExpiringThisMonth(): array
    {
        $endOfThisMonth = new \DateTime(date('Y-m-t'));
        return $this->repository->findMembersExpiringByDate($endOfThisMonth);
    }

    /**
     * Get an array of all members who have elected to receive a copy of Hume Studies in the post.
     *
     * @return User[]
     */
    public function getMembersReceivingHumeStudies(): array
    {
        return $this->repository->findMembersReceivingHumeStudies();
    }

    /**
     * Get one user by their username.
     *
     * @return User|null
     */
    public function getUserByUsername($username): ?User
    {
        return $this->repository->findOneByUsername($username);
    }

    /**
     * Get one user by their email.
     *
     * @return User|null
     */
    public function getUserByEmail($email): ?User
    {
        return $this->repository->findOneByEmail($email);
    }

    /**
     * Get an array of all users who have indicated a willingness to receieve invitations to review.
     *
     * @return User[]
     */
    public function getReviewVolunteers(): array
    {
        return $this->repository->findReviewVolunteers();
    }

    /**
     * Get an array of all users who have volunteered to comment on a paper at the next Hume Conference.
     *
     * @return User[]
     */
    public function getCommentVolunteers(): array
    {
        return $this->repository->findCommentVolunteers();
    }

    /**
     * Get an array of all users who have volunteered to chair a session at the next Hume Conference.
     *
     * @return User[]
     */
    public function getChairVolunteers(): array
    {
        return $this->repository->findChairVolunteers();
    }

    /**
     * Save/update a user in the database.
     *
     * @param User The user to save/update.
     * @return void
     */
    public function saveUser(User $user)
    {
        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * Delete a user from the database.
     *
     * @param User The user to delete.
     * @return void
     */
    public function deleteUser(User $user)
    {
        foreach ($user->getCandidacies() as $candidate) {
            // keep a record of their candidacy (for the society's records), but remove the explicit
            // user association
            $this->candidateHandler->unlinkUser($candidate);
        }
        foreach ($user->getSubmissions() as $submission) {
            // delete the user's submissions
            $this->submissionHandler->deleteSubmission($submission);
        }
        foreach ($user->getReviews() as $review) {
            // keep the review (in case the person reviewed wants to see it), but remove the
            // explicit user association
            $this->reviewHandler->unlinkUser($review);
        }
        $this->manager->remove($user);
        $this->manager->flush();
    }

    /**
     * Update a user's last login to today's date.
     *
     * @param User The user to be updated.
     * @return void
     */
    public function updateLastLogin(User $user)
    {
        $user->setLastLogin(new \DateTime());
        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * Update a user's dues payment date.
     *
     * @param User The user to be updated.
     * @param DuesPayment The payment record.
     */
    public function updateDues(User $user, DuesPayment $duesPayment)
    {
        switch ($duesPayment->getDescription()) {
            case 'Regular Membership (1 year)': // fallthrough
            case 'Student Membership (1 year)':
                $user->setDues(1);
                break;
            case 'Regular Membership (2 years)': // fallthrough
            case 'Student Membership (2 years)':
                $user->setDues(2);
                break;
            case 'Regular Membership (5 years)':
                $user->setDues(5);
                break;
        }

        $user->addRole('ROLE_MEMBER');

        $this->manager->persist($user);
        $this->manager->flush();
    }
}
