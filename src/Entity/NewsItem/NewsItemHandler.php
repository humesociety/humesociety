<?php

namespace App\Entity\NewsItem;

use Doctrine\ORM\EntityManagerInterface;

class NewsItemHandler
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(NewsItem::class);
    }

    public function getNewsItems(): array
    {
        return $this->repository->findAll();
    }

    public function getCurrentNewsItems(string $category = null): array
    {
        if ($category != null) {
            return $this->repository->findCurrentByCategory($category);
        }
        return $this->repository->findCurrent();
    }

    public function getArchivedNewsItems(string $category = null): array
    {
        if ($category != null) {
            return $this->repository->findArchivedByCategory($category);
        }
        return $this->repository->findArchived();
    }

    public function getYears(): array
    {
        $years = [];
        $dates = $this->repository->findNewsItemDates();
        foreach ($dates as $date) {
            $years[] = $date->format('Y');
        }
        return array_unique($years);
    }

    public function saveNewsItem(NewsItem $newsItem)
    {
        $this->manager->persist($newsItem);
        $this->manager->flush();
    }

    public function deleteNewsItem(NewsItem $newsItem)
    {
        $this->manager->remove($newsItem);
        $this->manager->flush();
    }
}
