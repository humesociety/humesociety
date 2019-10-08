<?php

namespace App\Service;

use App\Entity\Conference\Conference;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use App\Entity\Upload\Upload;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The conference handler contains the main business logic for reading and writing conference data.
 */
class ConferenceManager
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * The upload manager.
     *
     * @var string
     */
    private $uploads;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param UploadManager The upload manager.
     * @return void
     */
    public function __construct(EntityManagerInterface $em, UploadManager $uploads)
    {
        $this->em = $em;
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
        $this->em->refresh($conference);
        return $this->enrichConference($conference);
    }

    /**
     * Refresh a submission.
     *
     * @param Submission The submission to refresh.
     * @return Submission
     */
    public function refreshSubmission(Submission $submission)
    {
        $this->em->refresh($submission);
        return $submission;
    }

    /**
     * Get an array of all conferences (enriched with associated uploads).
     *
     * @return Conference[]
     */
    public function getConferences(): array
    {
        return array_map(function ($x) {
            return $this->enrichConference($x);
        }, $this->em->getRepository(Conference::class)->findAll());
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
        $forthcoming = $this->em->getRepository(Conference::class)->findConferencesForThisYearAndLater();

        // ... so now we may need to remove this year's conference if it's past
        if (sizeof($forthcoming) > 0
            && $forthcoming[0]->getEndDate()
            && $forthcoming[0]->getEndDate() < new \DateTime()
        ) {
            array_shift($forthcoming);
        }

        // and return what's left
        return array_map(function ($x) {
            return $this->enrichConference($x);
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
        return $this->em->getRepository(Conference::class)->findLatestNumber() + 1;
    }

    /**
     * Get the year of the next conference not in the database.
     *
     * @return int
     */
    public function getNextYear(): int
    {
        return $this->em->getRepository(Conference::class)->findLatestYear() + 1;
    }

    /**
     * Get an array of decades for which there are conferences in the database.
     *
     * @return int[]
     */
    public function getDecades(): array
    {
        return $this->em->getRepository(Conference::class)->findDecades();
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
     * Get all reviewers.
     *
     * @return Reviewer[]
     */
    public function getReviewers(): array
    {
        return $this->em->getRepository(Reviewer::class)->findAll();
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
     * Save/upload a conference file.
     *
     * @param Upload The upload to save.
     * @param Conference The conference.
     */
    public function saveConferenceFile(Upload $upload)
    {
        $this->uploads->saveUpload($upload);
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
    }

    /**
     * Save/update a conference in the database.
     *
     * @param Conference The conference to save/update.
     */
    public function saveConference(Conference $conference)
    {
        $this->em->persist($conference);
        $this->em->flush();
    }

    /**
     * Delete a conference from the database.
     *
     * @param Conference The conference to delete.
     */
    public function deleteConference(Conference $conference)
    {
        $conference = $this->enrichConference($conference);
        foreach ($conference->getUploads() as $upload) {
            $uploadHandler->deleteConferenceFile($upload);
        }
        $this->em->remove($conference);
        $this->em->flush();
    }

    /**
     * Save/update a submission.
     *
     * @param Submission The submission to save/update.
     */
    public function saveSubmission(Submission $submission)
    {
        $this->em->persist($submission);
        $this->em->flush();
    }

    /**
     * Save/update a review.
     *
     * @param Review The submission to save/update.
     */
    public function saveReview(Review $review)
    {
        $this->em->persist($review);
        $this->em->flush();
        $this->refreshSubmission($review->getSubmission());
    }
}
