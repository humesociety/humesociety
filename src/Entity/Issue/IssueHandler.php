<?php

namespace App\Entity\Issue;

use App\Entity\Article\ArticleHandler;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The issue handler contains the main business logic for reading and writing journal issue data.
 */
class IssueHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The issue repository.
     *
     * @var IssueRepository
     */
    private $repository;

    /**
     * The article handler.
     *
     * @var ArticleHandler
     */
    private $articles;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param ArticleHandler The article handler.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, ArticleHandler $articles)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Issue::class);
        $this->articleHandler = $articles;
    }

    /**
     * Get all issues.
     *
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->repository->findBy([], ['volume' => 'ASC', 'number' => 'ASC']);
    }

    /**
     * Get all issues in reverse order.
     *
     * @return Issue[]
     */
    public function getIssuesReversed(): array
    {
        return $this->repository->findBy([], ['volume' => 'DESC', 'number' => 'DESC']);
    }

    /**
     * Get an issue from its volume and number.
     *
     * @param int The volume of the issue.
     * @param int The number of the issue.
     * @return Issue|null
     */
    public function getIssue(int $volume, int $number): ?Issue
    {
        return $this->repository->createQueryBuilder('i')
            ->andWhere('i.volume = :volume')
            ->andWhere('i.number = :number')
            ->setParameter('volume', $volume)
            ->setParameter('number', $number)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the latest (i.e. most recent) issue.
     *
     * @return Issue|null
     */
    public function getLatestIssue(): ?Issue
    {
        return $this->repository->createQueryBuilder('i')
            ->orderBy('i.volume', 'DESC')
            ->addOrderBy('i.number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the volume of the latest (i.e. most recent) issue.
     *
     * @return int|null
     */
    public function getLatestVolume(): ?int
    {
        $latest = $this->getLatestIssue();
        return $latest ? $latest->getVolume() : null;
    }

    /**
     * Get the decades of all issues.
     *
     * @return int[]
     */
    public function getDecades(): array
    {
        $decades = $this->repository->createQueryBuilder('i')
            ->select('DISTINCT ((i.volume + 1974) - MOD((i.volume + 1974), 10)) AS decade')
            ->orderBy('decade', 'DESC')
            ->getQuery()
            ->getScalarResult();
        return array_map('current', $decades);
    }

    /**
     * Create the next issue.
     *
     * @return Issue
     */
    public function createNextIssue(): Issue
    {
        $issue = new Issue();
        $latest = $this->getLatestIssue();
        if ($latest) {
            if ($latest->getNumber() === 1) {
                $issue->setVolume($latest->getVolume());
                $issue->setNumber(2);
            } else {
                $issue->setVolume($latest->getVolume() + 1);
                $issue->setNumber(1);
            }
        } else {
            $issue->setVolume(1);
            $issue->setNumber(1);
        }
        return $issue;
    }

    /**
     * Save/update an issue.
     *
     * @param Issue The issue to save/update.
     * @return void
     */
    public function saveIssue(Issue $issue)
    {
        $this->manager->persist($issue);
        $this->manager->flush();
    }

    /**
     * Delete an issue.
     *
     * @param Issue The issue to delete.
     * @return void
     */
    public function deleteIssue(Issue $issue)
    {
        foreach ($issue->getArticles() as $article) {
            $this->articles->deleteArticle($article);
        }
        $this->manager->remove($issue);
        $this->manager->flush();
    }
}
