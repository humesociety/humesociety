<?php

namespace App\Entity\Conference;

use App\Entity\Upload\UploadHandler;
use Doctrine\ORM\EntityManagerInterface;

class ConferenceHandler
{
    private $manager;
    private $repository;
    private $uploadHandler;

    // Constructor function
    public function __construct(
        EntityManagerInterface $manager,
        UploadHandler $uploadHandler
    ) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Conference::class);
        $this->uploadHandler = $uploadHandler;
    }

    // Enrich a conference (i.e. link associated uploads)
    public function enrich(?Conference $conference): ?Conference
    {
        if ($conference) {
            $conference->setUploads($this->uploadHandler->getConferenceUploads($conference));
        }

        return $conference;
    }

    // Getters
    public function getConferences(): array
    {
        return array_map(function ($x) {
            return $this->enrich($x);
        }, $this->repository->findAll());
    }

    public function getForthcomingConferences(): array
    {
        // the repository returns conferences for the current year and later
        // (that's the best we can do, since forthcoming conferences may not have a specific date)
        $forthcoming = $this->repository->findForthcoming();
        // remove this year's conference if it's past
        if ($forthcoming[0] && $forthcoming[0]->getEndDate() && $forthcoming[0]->getEndDate() < new \DateTime()) {
            array_shift($forthcoming);
        }
        return array_map(function ($x) {
            return $this->enrich($x);
        }, $forthcoming);
    }

    public function getCurrentConference(): ?Conference
    {
        $forthcoming = $this->getForthcomingConferences();
        return array_shift($forthcoming);
    }

    public function getNextNumber(): ?int
    {
        return $this->repository->findLatestNumber() + 1;
    }

    public function getNextYear(): ?int
    {
        return $this->repository->findLatestYear() + 1;
    }

    public function getDecades(): array
    {
        return $this->repository->findDecades();
    }

    // Update database
    public function saveConference(Conference $conference)
    {
        $this->manager->persist($conference);
        $this->manager->flush();
    }

    public function deleteConference(Conference $conference)
    {
        $this->manager->remove($conference);
        $this->manager->flush();
    }
}
