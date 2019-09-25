<?php

namespace App\Entity\Submission;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
     * The submissions directory.
     *
     * @var string
     */
    private $submissionsDirectory;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param ParameterBagInterface Symfony's paramater bag interface.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, ParameterBagInterface $params)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Submission::class);
        $this->submissionsDirectory = $params->get('uploads_directory').'submissions/';
    }

    /**
     * Save/update a submission in the database.
     *
     * @param Submission The submission to save/update.
     */
    public function saveSubmission(Submission $submission)
    {
        if ($submission->getFile()) {
            $path = $this->submissionsDirectory.$submission->getPath();
            $submission->getFile()->move($path, $submission->getFilename());
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
        $fullpath = $this->submissionsDirectory.$article->getPath().$article->getFilename();
        if (file_exists($fullpath)) {
            $fs = new FileSystem();
            $fs->remove($fullpath);
        }
        $this->manager->remove($submission);
        $this->manager->flush();
    }
}
