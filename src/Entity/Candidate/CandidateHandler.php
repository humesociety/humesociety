<?php

namespace App\Entity\Candidate;

use Doctrine\ORM\EntityManagerInterface;

class CandidateHandler
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Candidate::class);
    }

    public function getEvpts(): array
    {
        return $this->repository->findEVPTs();
    }

    public function getExecs(): array
    {
        return $this->repository->findExecs();
    }

    public function getYears(): array
    {
        return range($this->repository->findFirstYear(), idate('Y'), 1);
    }

    public function getCandidatesByYear(int $year): array
    {
        return $this->repository->findCandidatesByYear($year);
    }

    public function saveCandidate(Candidate $candidate)
    {
        $this->manager->persist($candidate);
        $this->manager->flush();
    }

    public function deleteCandidate(Candidate $candidate)
    {
        $this->manager->remove($candidate);
        $this->manager->flush();
    }

    public function electCandidate(Candidate $candidate)
    {
        $candidate->setElected(true);
        $this->manager->persist($candidate);
        $this->manager->flush();
    }
}
