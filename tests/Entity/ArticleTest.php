<?php

namespace App\Tests\Entity;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleHandler;
use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the article entity and related services.
 */
class ArticleTest extends WebTestCase
{
    public function testGettersAndSetters()
    {
        $issue = new Issue();
        $issue->setVolume(1)
            ->setNumber(1);
        $article = new Article();
        $article->setIssue($issue)
            ->setPosition(1)
            ->setTitle('Article Title')
            ->setAuthors('Article Authors')
            ->setStartPage(1)
            ->setEndPage(10)
            ->setMuseId(123456)
            ->setDoi('doi');
        $this->assertEquals($article->getTitle(), (string) $article);
        $this->assertEquals($issue, $article->getIssue());
        $this->assertEquals(1, $article->getPosition());
        $this->assertEquals('Article Title', $article->getTitle());
        $this->assertEquals('Article Authors', $article->getAuthors());
        $this->assertEquals(1, $article->getStartPage());
        $this->assertEquals(10, $article->getEndPage());
        $this->assertEquals(123456, $article->getMuseId());
        $this->assertEquals('doi', $article->getDoi());
        $this->assertEquals('123456.pdf', $article->getFilename());
        $this->assertEquals('issues/v1n1/', $article->getPath());
    }
}
