<?php

namespace App\Entity\Issue;

use App\Entity\Article\ArticleHandler;
use App\Entity\Note\NoteHandler;
use Doctrine\ORM\EntityManagerInterface;

class IssueHandler
{
    private $manager;
    private $repository;
    private $articleHandler;

    public function __construct(
        EntityManagerInterface $manager,
        ArticleHandler $articleHandler
    ) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Issue::class);
        $this->articleHandler = $articleHandler;
    }

    public function getIssues(): array
    {
        return $this->repository->findAll();
    }

    public function getIssuesReversed(): array
    {
        return $this->repository->findAllReversed();
    }

    public function getIssue(int $volume, int $number): ?Issue
    {
        return $this->repository->findOneByVolumeAndNumber($volume, $number);
    }

    public function getLatestVolume(): ?int
    {
        $latest = $this->repository->findLatestIssue();
        return $latest ? $latest->getVolume() : null;
    }

    public function getDecades(): array
    {
        return $this->repository->findDecades();
    }

    public function setNextVolumeAndNumber(Issue $issue)
    {
        $latest = $this->repository->findLatestIssue();
        if ($latest) {
            if ($latest->getNumber() == 1) {
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
    }

    public function saveIssue(Issue $issue)
    {
        $this->manager->persist($issue);
        $this->manager->flush();
    }

    public function deleteIssue(Issue $issue)
    {
        foreach ($issue->getArticles() as $article) {
            $this->articleHandler->deleteArticle($article);
        }
        $this->manager->remove($issue);
        $this->manager->flush();
    }
}
