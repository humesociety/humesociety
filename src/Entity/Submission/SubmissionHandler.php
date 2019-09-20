<?php

namespace App\Entity\Submission;

use Doctrine\ORM\EntityManagerInterface;

/**
 * The sumission handler contains the main business logic for reading and writing submission data.
 */
class SubmissionHandler
{
    /**
     * The Doctrine entity manager (dependency injection).
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The submission repository (dependency injection).
     *
     * @var SubmissionRepository
     */
    private $repository;

    /**
     * The upload handler (dependency injection).
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
        $this->repository = $manager->getRepository(Submission::class);
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * Save/update a submission in the database.
     *
     * @param Submission The submission to save/update.
     */
    public function saveSubmission(Submission $submission)
    {
        if ($submission->getFile()) {
            $this->uploadHandler->saveSubmissionFile($submission);
        }
        $this->manager->persist($submission);
        $this->manager->flush();
    }

    /**
     * Delete a submission from the database.
     *
     * @param Submission The submission to delete.
     */
    public function deleteSubmission(Submission $submission)
    {
        $uploadHandler->deleteSubmissionFile($submission);
        $this->manager->remove($submission);
        $this->manager->flush();
    }
}
