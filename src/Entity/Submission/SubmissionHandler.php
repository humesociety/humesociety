<?php

namespace App\Entity\Submission;

use App\Entity\Conference\Conference;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The sumission handler contains the main business logic for reading and writing submission data.
 */
class SubmissionHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The submission repository.
     *
     * @var SubmissionRepository
     */
    private $repository;

    /**
     * The submissions directory.
     *
     * @var string
     */
    private $uploadsDirectory;

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
        $this->uploadsDirectory = $params->get('uploads_directory');
    }

    /**
     * Get the submission for a given user and conference (possibly null).
     *
     * @param User The user.
     * @param Conference The conference.
     * @return Submission|null
     */
    public function getSubmission(User $user, Conference $conference): ?Submission
    {
        return $this->repository->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.conference = :conference')
            ->setParameter('user', $user)
            ->setParameter('conference', $conference)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Save/update a submission in the database.
     *
     * @param Submission The submission to save/update.
     */
    public function saveSubmission(Submission $submission)
    {
        if ($submission->getFile()) {
            $path = $this->uploadsDirectory.$submission->getPath();
            $submission->getFile()->move($path, $submission->getFilename());
            $submission->setFile(null);
        }
        $this->manager->persist($submission);
        $this->manager->flush();
    }

    /**
     * Refresh a submission.
     *
     * @var Submission The submission to refresh.
     * @return void
     */
    public function refreshSubmission(Submission $submission)
    {
        $this->manager->refresh($submission);
    }

    /**
     * Delete a submission from the database.
     *
     * @param Submission The submission to delete.
     */
    public function deleteSubmission(Submission $submission)
    {
        $fullpath = $this->uploadsDirectory.$submission->getPath().$submission->getFilename();
        if (file_exists($fullpath)) {
            $fs = new FileSystem();
            $fs->remove($fullpath);
        }
        $this->manager->remove($submission);
        $this->manager->flush();
    }
}
