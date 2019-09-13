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
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');
        $nextYearString = (string) ($currentYear + 1);
        if ($currentMonth <= 6) {
            $endDate = new \DateTime($nextYearString.'-12-31');
        } else {
            $endDate = new \DateTime($nextYearString.'-06-30');
        }

        switch ($duesPayment->getDescription()) {
            case 'Regular Membership (1 year)': // fallthrough
            case 'Student Membership (1 year)':
                $user->setDues($endDate);
                break;
            case 'Regular Membership (2 years)': // fallthrough
            case 'Student Membership (2 years)':
                $endDate->add(new \DateInterval('P1Y'));
                $user->setDues($endDate);
                break;
            case 'Regular Membership (5 years)':
                $endDate->add(new \DateInterval('P4Y'));
                $user->setDues($endDate);
                break;
        }

        $user->addRole('ROLE_MEMBER');

        $this->manager->persist($user);
        $this->manager->flush();
    }
}
