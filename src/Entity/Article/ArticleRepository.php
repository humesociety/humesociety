<?php

namespace App\Entity\Article;

use App\Entity\Issue\Issue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * The article repository.
 *
 * Controllers should not interact with the article repository directly, but instead use the article
 * handler. The latter injects this class as a dependency, and exposes all the necessary
 * functionality.
 */
class ArticleRepository extends ServiceEntityRepository
{
    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Find the article preceeding the given article in an issue.
     *
     * @param Article The article.
     * @return Article|null
     */
    public function findPreviousArticle(Article $article): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.issue = :issue')
            ->andWhere('a.position = :position')
            ->setParameter('issue', $article->getIssue())
            ->setParameter('position', $article->getPosition() - 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find the article following the given article in an issue.
     *
     * @param Article The article.
     * @return Article|null
     */
    public function findNextArticle(Article $article): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.issue = :issue')
            ->andWhere('a.position = :position')
            ->setParameter('issue', $article->getIssue())
            ->setParameter('position', $article->getPosition() + 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find how many articles there are in an issue.
     *
     * @param Issue The issue.
     * @return int
     */
    private function countArticles(Issue $issue): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.issue = :issue')
            ->setParameter('issue', $issue)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find the position of the last article in an issue.
     *
     * @param Issue The issue.
     * @return int
     */
    public function findLastArticlePosition(Issue $issue): int
    {
        if ($this->countArticles($issue) == 0) {
            return 0;
        }

        return $this->createQueryBuilder('p')
            ->select('MAX(p.position)')
            ->where('p.issue = :issue')
            ->setParameter('issue', $issue->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
