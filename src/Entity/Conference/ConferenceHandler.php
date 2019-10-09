<?php

namespace App\Entity\Conference;

use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadHandler;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The conference hander contains the main business logic for reading and writing conference data.
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
    private $uploads;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param UploadHandler The upload handler.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, UploadHandler $uploads)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Conference::class);
        $this->uploads = $uploads;
    }

    /**
     * Enrich a conference (i.e. link associated uploads).
     *
     * @param Conference|null
     * @return Conference|null
     */
    public function enrichConference(?Conference $conference): ?Conference
    {
        if ($conference) {
            $conference->setUploads($this->uploads->getConferenceUploads($conference));
        }
        return $conference;
    }

    /**
     * Refresh a conference.
     *
     * @param Conference The conference to refresh.
     * @return Conference
     */
    public function refreshConference(Conference $conference)
    {
        $this->manager->refresh($conference);
        return $this->enrichConference($conference);
    }

    /**
     * Get an array of all conferences (enriched with associated uploads).
     *
     * @return Conference[]
     */
    public function getConferences(): array
    {
        $conferences = $this->repository->createQueryBuilder('c')
            ->orderBy('c.year', 'DESC')
            ->getQuery()
            ->getResult();
        return array_map('self::enrichConference', $conferences);
    }

    /**
     * Get an array of all forthcoming conferences (enriched with associated uploads).
     *
     * @return Conference[]
     */
    public function getForthcomingConferences(): array
    {
        // first get conferences for the current year and later ...
        $forthcoming = $this->repository->createQueryBuilder('c')
            ->where('c.year >= :thisYear')
            ->setParameter('thisYear', idate('Y'))
            ->orderBy('c.year', 'ASC')
            ->getQuery()
            ->getResult();
        // now we may need to remove this year's conference if it's past
        if (sizeof($forthcoming) > 0
            && $forthcoming[0]->getEndDate()
            && $forthcoming[0]->getEndDate() < new \DateTime()
        ) {
            array_shift($forthcoming);
        }
        return array_map('self::enrichConference', $forthcoming);
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
     * Find how many conferences there are in the database.
     *
     * @return int
     */
    private function countConferences(): int
    {
        return $this->repository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get an array of decades for which there are conferences in the database.
     *
     * @return int[]
     */
    public function getDecades(): array
    {
        if ($this->countConferences() == 0) {
            return [];
        }
        $decades = $this->createQueryBuilder('c')
            ->select('DISTINCT (c.year - MOD(c.year, 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
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
     * Create the next conference.
     *
     * @return Conference
     */
    public function createNextConference(): Conference
    {
        if ($this->countConferences() == 0) {
            $nextNumber = 1;
            $nextYear = date('Y');
        } else {
            $nextNumber = $this->repository->createQueryBuilder('c')
                ->select('MAX(c.number)')
                ->getQuery()
                ->getSingleScalarResult() + 1;
            $nextYear = $this->repository->createQueryBuilder('c')
                ->select('MAX(c.year)')
                ->getQuery()
                ->getSingleScalarResult() + 1;
        }
        $conference = new Conference();
        $conference->setNumber($nextNumber)->setYear($nextYear);
        return $conference;
    }

    /**
     * Create a conference file.
     *
     * @param Conference The conference to link to the file.
     */
    public function createConferenceFile(Conference $conference)
    {
        $upload = new Upload();
        $upload->setPath($conference->getPath());
        return $upload;
    }

    /**
     * Save/update a conference.
     *
     * @param Conference The conference to save/update.
     * @param int The conference's old path (in case it has changed).
     */
    public function saveConference(Conference $conference, string $oldPath)
    {
        $this->manager->persist($conference);
        $this->manager->flush();
        if ($conference->getPath() !== $oldPath) {
            $this->uploads->moveFiles($oldPath, $conference->getPath());
        }
        $this->refreshConference($conference);
    }

    /**
     * Save/upload a conference file.
     *
     * @param Upload The upload to save.
     * @param Conference The conference.
     */
    public function saveConferenceFile(Upload $upload, Conference $conference)
    {
        $this->uploads->saveUpload($upload);
        $this->refreshConference($conference);
    }

    /**
     * Delete a conference.
     *
     * @param Conference The conference to delete.
     */
    public function deleteConference(Conference $conference)
    {
        $conference = $this->enrich($conference);
        foreach ($conference->getUploads() as $upload) {
            $this->uploads->deleteUpload($upload);
        }
        $this->manager->remove($conference);
        $this->manager->flush();
    }

    /**
     * Delete a conference file.
     *
     * @param string The name of the conference file to delete.
     * @param Conference The conference.
     */
    public function deleteConferenceFile(string $filename, Conference $conference)
    {
        $upload = new Upload($conference->getPath(), $filename);
        $this->uploads->deleteUpload($upload);
        $this->refreshConference($conference);
    }
}
