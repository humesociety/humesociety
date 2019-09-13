<?php

namespace App\Entity\Article;

use App\Entity\Issue\Issue;
use App\Entity\Upload\UploadHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;

class ArticleHandler
{
    private $manager;
    private $repository;
    private $uploadHandler;

    public function __construct(EntityManagerInterface $manager, UploadHandler $uploadHandler)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Article::class);
        $this->uploadHandler = $uploadHandler;
    }

    public function getNextArticlePosition(Issue $issue): int
    {
        $lastArticlePosition = $this->repository->findLastArticlePosition($issue);
        return $lastArticlePosition ? $lastArticlePosition + 1 : 1;
    }

    public function setDataFromDoi(Article $article)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://api.crossref.org/v1/works/' . $article->doi);

        if ($response->getStatusCode() == 200) {
            $content = json_decode($response->getContent());
            if ($content->status == 'ok') {
                $article->setMetaData($content->message);
                $article->setTitle($content->message->title[0]);
                $authors = array_map(function ($author) {
                    return ($author->given) ? $author->given.' '.$author->family : $author->family;
                }, $content->message->author);
                $article->setAuthors(implode(', ', $authors));
                $pages = explode('-', $content->message->page);
                $article->setStartPage($pages[0]);
                $article->setEndPage($pages[1]);
            }
        }
    }

    public function saveArticle(Article $article)
    {
        if ($article->getFile()) {
            $this->uploadHandler->saveArticleFile($article);
        }
        $this->manager->persist($article);
        $this->manager->flush();
    }

    public function renameArticleFile(Article $article, string $oldFilename)
    {
        $this->uploadHandler->renameArticleFile($article, $oldFilename);
    }

    public function deleteArticle(Article $article)
    {
        $uploadHandler->deleteArticleFile($article);
        $this->manager->remove($article);
        $this->manager->flush();
    }

    private function swapArticles(Article $article1, Article $article2)
    {
        $article1->setPosition($article2->getPosition());
        $article2->setPosition($article2->getPosition() - 1);
        $this->manager->persist($article1);
        $this->manager->persist($article2);
        $this->manager->flush();
    }

    public function moveArticleUp(Article $article)
    {
        if ($article->getPosition() > 1) {
            $previous = $this->repository->findPreviousArticle($article);
            if ($previous) {
                $this->swapArticles($previous, $article);
            }
        }
    }

    public function moveArticleDown(Article $article)
    {
        if ($article->getPosition() < $this->getNextArticlePosition($article->getIssue()) - 1) {
            $next = $this->repository->findNextArticle($article);
            if ($next) {
                $this->swapArticles($article, $next);
            }
        }
    }
}
