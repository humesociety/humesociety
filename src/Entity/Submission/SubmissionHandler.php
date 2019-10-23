<?php

namespace App\Entity\Submission;

use App\Entity\Conference\Conference;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The submission handler contains the main business logic for reading and writing submission data.
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
     * @var EntityRepository
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
     * @param EntityManagerInterface $manager The Doctrine entity manager.
     * @param ParameterBagInterface $params Symfony's paramater bag interface.
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
     * @param User $user The user.
     * @param Conference $conference The conference.
     * @throws \Doctrine\ORM\NonUniqueResultException
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
     * Get the accepted submissions for the given conference.
     *
     * @param Conference $conference The conference.
     * @return Submission[]
     */
    public function getAcceptedSubmissions(Conference $conference): array
    {
        return $this->repository->createQueryBuilder('s')
            ->where('s.accepted = TRUE')
            ->andWhere('s.conference = :conference')
            ->setParameter('conference', $conference)
            ->getQuery()
            ->getResult();
    }

    /**
     * Save/update a submission in the database.
     *
     * @param Submission The submission to save/update.
     * @return void
     */
    public function saveSubmission(Submission $submission)
    {
        if ($submission->getFile()) {
            $path = $this->uploadsDirectory.$submission->getPath();
            $submission->getFile()->move($path, $submission->getFilename());
            $submission->setFile(null);
        }
        if ($submission->getFinalFile()) {
            $path = $this->uploadsDirectory.$submission->getPath().'final/';
            $submission->getFinalFile()->move($path, $submission->getFinalFilename());
            $submission->setFinalFile(null);
        }
        $this->manager->persist($submission);
        $this->manager->flush();
    }

    /**
     * Delete a submission from the database.
     *
     * @param Submission $submission The submission to delete.
     * @return void
     */
    public function deleteSubmission(Submission $submission)
    {
        $fullPath = $this->uploadsDirectory.$submission->getPath().$submission->getFilename();
        if (file_exists($fullPath)) {
            $fs = new FileSystem();
            $fs->remove($fullPath);
        }
        $this->manager->remove($submission);
        $this->manager->flush();
    }
}
