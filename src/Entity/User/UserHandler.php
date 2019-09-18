<?php

namespace App\Entity\User;

use App\Entity\DuesPayment\DuesPayment;
use Doctrine\ORM\EntityManagerInterface;

class UserHandler
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(User::class);
    }

    public function getUsers(): array
    {
        return $this->repository->findAll();
    }

    public function getMembers(): array
    {
        return $this->repository->findMembers();
    }

    public function getMembersInGoodStanding(): array
    {
        return $this->repository->findMembersInGoodStanding();
    }

    public function getMembersInArrears(): array
    {
        return $this->repository->findMembersInArrears();
    }

    public function getMembersExpiringThisMonth(): array
    {
        $endOfThisMonth = new \DateTime(date('Y-m-t'));
        return $this->repository->findMembersExpiringByDate($endOfThisMonth);
    }

    public function getMembersReceivingHumeStudies(): array
    {
        return $this->repository->findMembersReceivingHumeStudies();
    }

    public function getUserByUsername($username): ?User
    {
        return $this->repository->findOneByUsername($username);
    }

    public function getUserByEmail($email): ?User
    {
        return $this->repository->findOneByEmail($email);
    }

    public function saveUser(User $user)
    {
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function deleteUser(User $user)
    {
        $this->manager->remove($user);
        $this->manager->flush();
    }

    public function updateLastLogin(User $user)
    {
        $user->setLastLogin(new \DateTime());
        $this->manager->persist($user);
        $this->manager->flush();
    }

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
