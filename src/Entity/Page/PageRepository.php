<?php

namespace App\Entity\Page;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PageRepository extends ServiceEntityRepository
{
    private $sections;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.section, p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySectionAndSlug(string $section, string $slug): ?Page
    {
        return $this->createQueryBuilder('p')
            ->where('p.section = :section')
            ->andWhere('p.slug = :slug')
            ->setParameter('section', $section)
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySection(string $section): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.section = :section')
            ->setParameter('section', $section)
            ->getQuery()
            ->getResult();
    }

    public function findPreviousPage(Page $page): ?Page
    {
        return $this->createQueryBuilder('p')
            ->where('p.section = :section')
            ->andWhere('p.position = :position')
            ->setParameter('section', $page->getSection())
            ->setParameter('position', $page->getPosition() - 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextPage(Page $page): ?Page
    {
        return $this->createQueryBuilder('p')
            ->where('p.section = :section')
            ->andWhere('p.position = :position')
            ->setParameter('section', $page->getSection())
            ->setParameter('position', $page->getPosition() + 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextPages(Page $page): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.section = :section')
            ->andWhere('p.position > :position')
            ->setParameter('section', $page->getSection())
            ->setParameter('position', $page->getPosition())
            ->getQuery()
            ->getResult();
    }

    public function findLastPagePosition(string $section): ?int
    {
        return $this->createQueryBuilder('p')
            ->select('MAX(p.position)')
            ->where('p.section = :section')
            ->setParameter('section', $section)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
