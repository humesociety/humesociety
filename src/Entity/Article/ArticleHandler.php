<?php

namespace App\Entity\Article;

use App\Entity\Issue\Issue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

/**
 * The article handler contains the main business logic for reading and writing article data.
 */
class ArticleHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The article repository.
     *
     * @var EntityRepository
     */
    private $repository;

    /**
     * The path the the uploads directory.
     *
     * @var string
     */
    private $uploadsDirectory;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface $manager Doctrine's entity manager.
     * @param ParameterBagInterface $params Symfony's paramater bag interface.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, ParameterBagInterface $params)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Article::class);
        $this->uploadsDirectory = $params->get('uploads_directory');
    }

    /**
     * Find how many articles there are in an issue.
     *
     * @param Issue $issue The issue.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return int
     */
    private function countArticles(Issue $issue): int
    {
        return $this->repository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.issue = :issue')
            ->setParameter('issue', $issue)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Create the next article in an issue.
     *
     * @param Issue $issue The issue to create the article in.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return Article
     */
    public function createNextArticle(Issue $issue): Article
    {
        $article = new Article($issue);
        $article->setPosition($this->countArticles($issue) + 1);
        return $article;
    }

    /**
     * Set an article's metadata from its DOI.
     *
     * @param Article $article The article to modify.
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @return void
     */
    public function setDataFromDoi(Article $article)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://api.crossref.org/v1/works/' . $article->getDoi());
        if ($response->getStatusCode() === 200) {
            $content = json_decode($response->getContent());
            if ($content->status === 'ok') {
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

    /**
     * Save an article to the database.
     *
     * @param Article $article The article to save.
     * @return void
     */
    public function saveArticle(Article $article)
    {
        if ($article->getFile()) {
            $path = $this->uploadsDirectory.$article->getPath();
            $article->getFile()->move($path, $article->getFilename());
        }
        $this->manager->persist($article);
        $this->manager->flush();
    }

    /**
     * Rename the file associated with an article
     *
     * @param Article $article The article to modify (with the new filename).
     * @param string $oldFilename The article's previous filename.
     * @return void
     */
    public function renameArticleFile(Article $article, string $oldFilename)
    {
        $fullPath = $this->uploadsDirectory.$article->getPath();
        rename($fullPath.$oldFilename, $fullPath.$article->getFilename());
    }

    /**
     * Delete an article.
     *
     * @param Article $article The article to delete.
     * @return void
     */
    public function deleteArticle(Article $article)
    {
        $fullPath = $this->uploadsDirectory.$article->getPath().$article->getFilename();
        if (file_exists($fullPath)) {
            $fs = new FileSystem();
            $fs->remove($fullPath);
        }
        $this->manager->remove($article);
        $this->manager->flush();
    }

    /**
     * Swap the position of two articles.
     *
     * @param Article $article1 The first article.
     * @param Article $article2 The second article.
     * @return void
     */
    private function swapArticles(Article $article1, Article $article2)
    {
        $article1->setPosition($article2->getPosition());
        $article2->setPosition($article2->getPosition() - 1);
        $this->manager->persist($article1);
        $this->manager->persist($article2);
        $this->manager->flush();
    }

    /**
     * Get the article preceeding the given article in an issue.
     *
     * @param Article $article The article.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return Article|null
     */
    private function getPreviousArticle(Article $article): ?Article
    {
        return $this->repository->createQueryBuilder('a')
            ->where('a.issue = :issue')
            ->andWhere('a.position = :position')
            ->setParameter('issue', $article->getIssue())
            ->setParameter('position', $article->getPosition() - 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the article following the given article in an issue.
     *
     * @param Article $article The article.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return Article|null
     */
    private function getNextArticle(Article $article): ?Article
    {
        return $this->repository->createQueryBuilder('a')
            ->where('a.issue = :issue')
            ->andWhere('a.position = :position')
            ->setParameter('issue', $article->getIssue())
            ->setParameter('position', $article->getPosition() + 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Move an article up in an issue.
     *
     * @param Article $article The article to move.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return void
     */
    public function moveArticleUp(Article $article)
    {
        if ($article->getPosition() > 1) {
            $previous = $this->getPreviousArticle($article);
            if ($previous) {
                $this->swapArticles($previous, $article);
            }
        }
    }

    /**
     * Move an article down in an issue.
     *
     * @param Article $article The article to move.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return void
     */
    public function moveArticleDown(Article $article)
    {
        if ($article->getPosition() < $this->countArticles($article->getIssue())) {
            $next = $this->getNextArticle($article);
            if ($next) {
                $this->swapArticles($article, $next);
            }
        }
    }
}
