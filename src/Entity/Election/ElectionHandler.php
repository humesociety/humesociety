<?php

namespace App\Entity\Election;

use Doctrine\ORM\EntityManagerInterface;

class ElectionHandler
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Election::class);
    }

    public function getElections() : Array
    {
        return $this->repository->findAll();
    }

    public function getElectionByYear(int $year) : ?Election
    {
        return $this->repository->findElectionByYear($year);
    }

    public function getDecades() : Array
    {
        return $this->repository->findDecades();
    }

    public function saveElection(Election $election)
    {
        $this->manager->persist($election);
        $this->manager->flush();
    }

    public function deleteElection(Election $election)
    {
        $this->manager->remove($election);
        $this->manager->flush();
    }
}
