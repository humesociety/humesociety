<?php

namespace App\Entity\Page;

use Doctrine\ORM\EntityManagerInterface;

/**
 * The page handler contains the main business logic for reading and writing page data.
 */
class PageHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The page repository.
     *
     * @var PageRepository
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
        $this->repository = $manager->getRepository(Page::class);
    }

    /**
     * Get all pages.
     *
     * @return Page[]
     */
    public function getPages(): array
    {
        return $this->repository->createQueryBuilder('p')
            ->orderBy('p.section, p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all pages in a section.
     *
     * @return Page[]
     */
    public function getSectionPages(string $section): array
    {
        return $this->repository->createQueryBuilder('p')
            ->where('p.section = :section')
            ->setParameter('section', $section)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get a page by its section and slug.
     *
     * @return Page|null
     */
    public function getPage(string $section, string $slug): ?Page
    {
        return $this->repository->createQueryBuilder('p')
            ->where('p.section = :section')
            ->andWhere('p.slug = :slug')
            ->setParameter('section', $section)
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Create the next page in a section.
     *
     * @var string The section to create the page in.
     * @return Page
     */
    public function createNextPage(string $section)
    {
        $page = new Page($section);
        $page->setPosition(sizeof($this->getSectionPages($section)) + 1);
    }

    /**
     * Save/update a page.
     *
     * @var Page The page to save/update.
     * @return void
     */
    public function savePage(Page $page)
    {
        $this->manager->persist($page);
        $this->manager->flush();
    }

    /**
     * Delete a page.
     *
     * @var Page The page to delete.
     * @return void
     */
    public function deletePage(Page $page)
    {
        $this->manager->remove($page);
        $nextPages = $this->repository->createQueryBuilder('p')
            ->where('p.section = :section')
            ->andWhere('p.position > :position')
            ->setParameter('section', $page->getSection())
            ->setParameter('position', $page->getPosition())
            ->getQuery()
            ->getResult();
        foreach ($nextPages as $nextPage) {
            $nextPage->setPosition($nextPage->getPosition() - 1);
            $this->manager->persist($nextPage);
        }
        $this->manager->flush();
    }

    /**
     * Swap the position of two pages.
     *
     * @var Page The first page.
     * @var Page The second page.
     * @return void
     */
    private function swapPages(Page $page1, Page $page2)
    {
        $page1->setPosition($page2->getPosition());
        $page2->setPosition($page2->getPosition() - 1);
        $this->manager->persist($page1);
        $this->manager->persist($page2);
        $this->manager->flush();
    }

    /**
     * Move a page up in its section.
     *
     * @var Page The page to move.
     * @return void
     */
    public function movePageUp(Page $page)
    {
        if ($page->getPosition() > 2) {
            $previous = $this->repository->createQueryBuilder('p')
                ->where('p.section = :section')
                ->andWhere('p.position = :position')
                ->setParameter('section', $page->getSection())
                ->setParameter('position', $page->getPosition() - 1)
                ->getQuery()
                ->getOneOrNullResult();
            if ($previous) {
                $this->swapPages($previous, $page);
            }
        }
    }

    /**
     * Move a page down in its section.
     *
     * @var Page The page to move.
     * @return void
     */
    public function movePageDown(Page $page)
    {
        if ($page->getPosition() > 1) {
            $next = $this->repository->createQueryBuilder('p')
                ->where('p.section = :section')
                ->andWhere('p.position = :position')
                ->setParameter('section', $page->getSection())
                ->setParameter('position', $page->getPosition() + 1)
                ->getQuery()
                ->getOneOrNullResult();
            if ($next) {
                $this->swapPages($page, $next);
            }
        }
    }
}
