<?php

namespace App\Entity\NewsItem;

use Doctrine\ORM\EntityManagerInterface;

/**
 * The issue handler contains the main business logic for reading and writing journal issue data.
 */
class NewsItemHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The news item repository.
     *
     * @var NewsItemRepository
     */
    private $repository;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(NewsItem::class);
    }

    /**
     * Get all news items.
     *
     * @return NewsItem[]
     */
    public function getNewsItems(): array
    {
        return $this->repository->createQueryBuilder('n')
            ->orderBy('n.date', 'DESC')
            ->addOrderBy('n.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all current news items.
     *
     * @param string|null Optional category to restrict to.
     * @return NewsItem[]
     */
    public function getCurrentNewsItems(?string $category = null): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('n')
            ->where('n.end >= :now')->setParameter('now', new \DateTime('today'))
            ->orderBy('n.date', 'DESC')->addOrderBy('n.title', 'ASC');
        if ($category !== null) {
            $queryBuilder->andWhere('n.category = :category')->setParameter('category', $category);
        }
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get archived news items.
     *
     * @param string|null Optional category to restrict to.
     * @return NewsItem[]
     */
    public function getArchivedNewsItems(?string $category = null): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('n')
            ->where('n.end < :now')->setParameter('now', new \DateTime('today'))
            ->orderBy('n.date', 'DESC')->addOrderBy('n.title', 'ASC');
        if ($category !== null) {
            $queryBuilder->andWhere('n.category = :category')->setParameter('category', $category);
        }
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get years of all news items.
     *
     * @return int[]
     */
    public function getYears(): array
    {
        $years = [];
        $dates = $this->repository->createQueryBuilder('n')
            ->select('DISTINCT n.date')
            ->orderBy('n.date', 'DESC')
            ->getQuery()
            ->getResult();
        $dates = array_map('current', $dates);
        foreach ($dates as $date) {
            $years[] = $date->format('Y');
        }
        return array_unique($years);
    }

    /**
     * Save/update a news item.
     *
     * @param NewsItem The news item to save/update.
     * @return void
     */
    public function saveNewsItem(NewsItem $newsItem)
    {
        $this->manager->persist($newsItem);
        $this->manager->flush();
    }

    /**
     * Delete a news item.
     *
     * @param NewsItem The news item to delete.
     * @return void
     */
    public function deleteNewsItem(NewsItem $newsItem)
    {
        $this->manager->remove($newsItem);
        $this->manager->flush();
    }
}
