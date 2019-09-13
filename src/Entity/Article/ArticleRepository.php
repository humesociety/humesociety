<?php

namespace App\Entity\Article;

use App\Entity\Issue\Issue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findPreviousArticle(Article $article): ?Article
    {
        return $this->createQueryBuilder('n')
            ->where('n.issue = :issue')
            ->andWhere('n.position = :position')
            ->setParameter('issue', $article->getIssue())
            ->setParameter('position', $article->getPosition() - 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextArticle(Article $article): ?Article
    {
        return $this->createQueryBuilder('n')
            ->where('n.issue = :issue')
            ->andWhere('n.position = :position')
            ->setParameter('issue', $article->getIssue())
            ->setParameter('position', $article->getPosition() + 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastArticlePosition(Issue $issue): ?int
    {
        return $this->createQueryBuilder('p')
            ->select('MAX(p.position)')
            ->where('p.issue = :issue')
            ->setParameter('issue', $issue->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
