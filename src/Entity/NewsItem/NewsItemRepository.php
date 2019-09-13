<?php

namespace App\Entity\NewsItem;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class NewsItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NewsItem::class);
    }

    public function findAll() : Array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.date', 'DESC')
            ->addOrderBy('n.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCurrent() : Array
    {
        return $this->createQueryBuilder('n')
            ->where('n.end >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('n.date', 'DESC')
            ->addOrderBy('n.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCurrentByCategory(string $category) : Array
    {
        return $this->createQueryBuilder('n')
            ->where('n.end >= :now')
            ->andWhere('n.category = :category')
            ->setParameter('now', new \DateTime())
            ->setParameter('category', $category)
            ->orderBy('n.date', 'DESC')
            ->addOrderBy('n.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findArchived() : Array
    {
        return $this->createQueryBuilder('n')
            ->where('n.end < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('n.date', 'DESC')
            ->addOrderBy('n.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findArchivedByCategory(string $category) : Array
    {
        return $this->createQueryBuilder('n')
            ->where('n.end < :now')
            ->andWhere('n.category = :category')
            ->setParameter('now', new \DateTime())
            ->setParameter('category', $category)
            ->orderBy('n.date', 'DESC')
            ->addOrderBy('n.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findNewsItemDates() : Array
    {
        $dates = $this->createQueryBuilder('n')
            ->select('DISTINCT n.date')
            ->orderBy('n.date', 'DESC')
            ->getQuery()
            ->getResult();
        return array_map('current', $dates);
    }
}
