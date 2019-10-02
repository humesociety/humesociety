<?php

namespace App\Entity\Conference;

use App\Entity\Upload\UploadHandler;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The conference handler contains the main business logic for reading and writing conference data.
 */
class ConferenceHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The conference repository.
     *
     * @var ConferenceRepository
     */
    private $repository;

    /**
     * The upload handler.
     *
     * @var UploadHandler
     */
    private $uploadHandler;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param UploadHandler The upload handler.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, UploadHandler $uploadHandler)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Conference::class);
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * Enrich a conference (i.e. link associated uploads).
     *
     * @param Conference|null
     * @return Conference|null
     */
    public function enrich(?Conference $conference): ?Conference
    {
        if ($conference) {
            $conference->setUploads($this->uploadHandler->getConferenceUploads($conference));
        }

        return $conference;
    }

    /**
     * Get an array of all conferences (enriched with associated uploads).
     *
     * @return Conference[]
     */
    public function getConferences(): array
    {
        return array_map(function ($x) {
            return $this->enrich($x);
        }, $this->repository->findAll());
    }

    /**
     * Get an array of all forthcoming conferences (enriched with associated uploads).
     *
     * @return Conference[]
     */
    public function getForthcomingConferences(): array
    {
        // the repository function returns conferences for the current year and later ...
        // (that's the best we can do, since forthcoming conferences may not have a specific date)
        $forthcoming = $this->repository->findConferencesForThisYearAndLater();

        // ... so now we may need to remove this year's conference if it's past
        if (sizeof($forthcoming) > 0
            && $forthcoming[0]->getEndDate()
            && $forthcoming[0]->getEndDate() < new \DateTime()
        ) {
            array_shift($forthcoming);
        }

        return array_map(function ($x) {
            return $this->enrich($x);
        }, $forthcoming);
    }

    /**
     * Get the current conference (i.e. the earliest forthcoming conference).
     *
     * @return Conference|null
     */
    public function getCurrentConference(): ?Conference
    {
        $forthcoming = $this->getForthcomingConferences();
        return array_shift($forthcoming);
    }

    /**
     * Get the number of the next conference not in the database.
     *
     * @return int
     */
    public function getNextNumber(): int
    {
        return $this->repository->findLatestNumber() + 1;
    }

    /**
     * Get the year of the next conference not in the database.
     *
     * @return int
     */
    public function getNextYear(): int
    {
        return $this->repository->findLatestYear() + 1;
    }

    /**
     * Get an array of decades for which there are conferences in the database.
     *
     * @return int[]
     */
    public function getDecades(): array
    {
        return $this->repository->findDecades();
    }

    /**
     * Get the keywords of all submissions to the given conference.
     *
     * @param Conference The conference to check.
     * @return string[]
     */
    public function getSubmissionKeywords(Conference $conference): array
    {
        $keywords = [];
        foreach ($conference->getSubmissions() as $submission) {
            foreach (explode(', ', $submission->getKeywords()) as $k) {
                if (!in_array($k, $keywords)) {
                    $keywords[] = $k;
                }
            }
        }
        sort($keywords);
        return $keywords;
    }

    /**
     * Save/update a conference in the database.
     *
     * @param Conference The conference to be saved/updated.
     */
    public function saveConference(Conference $conference)
    {
        $this->manager->persist($conference);
        $this->manager->flush();
    }

    /**
     * Delete a conference from the database.
     *
     * @param Conference The conference to be deleted.
     */
    public function deleteConference(Conference $conference)
    {
        $conference = $this->enrich($conference);
        foreach ($conference->getUploads() as $upload) {
            $uploadHandler->deleteConferenceFile($upload);
        }

        $this->manager->remove($conference);
        $this->manager->flush();
    }
}
