<?php

namespace App\Entity\Paper;

use App\Entity\Conference\Conference;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The paper handler contains the main business logic for reading and writing paper data.
 */
class PaperHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The paper repository.
     *
     * @var EntityRepository
     */
    private $repository;

    /**
     * The uploads directory.
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
        $this->repository = $manager->getRepository(Paper::class);
        $this->uploadsDirectory = $params->get('uploads_directory');
    }

    /**
     * Get all papers.
     *
     * @param Conference|null $conference Optional conference to restrict to.
     * @return Paper[]
     */
    public function getPapers(?Conference $conference = null): array
    {
          if ($conference === null) {
              return $this->repository->findAll();
          }
          return $this->repository->createQueryBuilder('p')
              ->where('p.conference = :conference')
              ->setParameter('conference', $conference)
              ->getQuery()
              ->getResult();
    }

    /**
     * Get the paper for a given user and conference (possibly null).
     *
     * @param User $user The user.
     * @param Conference $conference The conference.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return Paper|null
     */
    public function getPaper(User $user, Conference $conference): ?Paper
    {
        return $this->repository->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.conference = :conference')
            ->setParameter('user', $user)
            ->setParameter('conference', $conference)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get a paper by its secret.
     *
     * @param string $secret The secret.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return Paper|null
     */
    public function getPaperBySecret(string $secret): ?Paper
    {
        return $this->repository->createQueryBuilder('p')
            ->where('p.secret = :secret')
            ->setParameter('secret', $secret)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Save/update a paper in the database.
     *
     * @param Paper $paper The paper to save/update.
     * @return void
     */
    public function savePaper(Paper $paper)
    {
        if ($paper->getFile()) {
            $path = $this->uploadsDirectory.$paper->getPath();
            $paper->getFile()->move($path, $paper->getFilename());
            $paper->setFile(null);
        }
        $this->manager->persist($paper);
        $this->manager->flush();
    }

    /**
     * Delete a paper.
     *
     * @param Paper $paper The paper to delete.
     */
    public function deletePaper(Paper $paper)
    {
        $fullPath = $this->uploadsDirectory.$paper->getPath().$paper->getFilename();
        if (file_exists($fullPath)) {
            $fs = new FileSystem();
            $fs->remove($fullPath);
        }
        $this->manager->remove($paper);
        $this->manager->flush();
    }
}
