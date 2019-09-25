<?php

namespace App\Entity\Page;

use Doctrine\ORM\EntityManagerInterface;

class PageHandler
{
    private $manager;
    private $repository;
    private $sections;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Page::class);
    }

    public function getPage(string $section, string $slug): ?Page
    {
        return $this->repository->findBySectionAndSlug($section, $slug);
    }

    public function getSectionPages(string $section): array
    {
        return $this->repository->findBySection($section);
    }

    public function getPages(): array
    {
        return $this->repository->findAll();
    }

    public function getNextPagePosition(string $section): int
    {
        $lastPagePosition = $this->repository->findLastPagePosition($section);
        return $lastPagePosition ? $lastPagePosition + 1 : 1;
    }

    public function savePage(Page $page)
    {
        $this->manager->persist($page);
        $this->manager->flush();
    }

    public function deletePage(Page $page)
    {
        $this->manager->remove($page);
        foreach ($this->repository->findNextPages($page) as $nextPage) {
            $nextPage->setPosition($nextPage->getPosition() - 1);
            $this->manager->persist($nextPage);
        }
        $this->manager->flush();
    }

    private function swapPages(Page $page1, Page $page2)
    {
        $page1->setPosition($page2->getPosition());
        $page2->setPosition($page2->getPosition() - 1);
        $this->manager->persist($page1);
        $this->manager->persist($page2);
        $this->manager->flush();
    }

    public function movePageUp(Page $page)
    {
        if ($page->getPosition() > 2) {
            $previous = $this->repository->findPreviousPage($page);
            if ($previous) {
                $this->swapPages($previous, $page);
            }
        }
    }

    public function movePageDown(Page $page)
    {
        if ($page->getPosition() > 1) {
            $next = $this->repository->findNextPage($page);
            if ($next) {
                $this->swapPages($page, $next);
            }
        }
    }
}
