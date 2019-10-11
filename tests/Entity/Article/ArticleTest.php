<?php

namespace App\Tests\Entity\Article;

use App\Entity\Article\Article;
use App\Entity\Issue\Issue;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the article entity.
 */
class ArticleTest extends WebTestCase
{
    private $issue;
    private $article;

    public function setUp()
    {
        $this->issue = new Issue();
        $this->issue->setVolume(1)->setNumber(1);
        $this->article = new Article($this->issue);
    }

    public function testConstructor()
    {
        $this->assertSame('uninitialised article', (string) $this->article);
        $this->assertSame(null, $this->article->getId());
        $this->assertSame($this->issue, $this->article->getIssue());
        $this->assertSame(null, $this->article->getPosition());
        $this->assertSame(null, $this->article->getTitle());
        $this->assertSame(null, $this->article->getAuthors());
        $this->assertSame(null, $this->article->getStartPage());
        $this->assertSame(null, $this->article->getEndPage());
        $this->assertSame(null, $this->article->getMuseId());
        $this->assertSame(null, $this->article->getDoi());
        $this->assertSame(null, $this->article->getFilename());
        $this->assertSame('issues/v1n1/', $this->article->getPath());
    }

    public function testGettersAndSetters()
    {
        $this->article->setPosition(1)
            ->setTitle('Article Title')
            ->setAuthors('Article Authors')
            ->setStartPage(1)
            ->setEndPage(10)
            ->setMuseId(123456)
            ->setDoi('doi');
        $this->assertSame('Article Title', (string) $this->article);
        $this->assertSame($this->issue, $this->article->getIssue());
        $this->assertSame(1, $this->article->getPosition());
        $this->assertSame('Article Title', $this->article->getTitle());
        $this->assertSame('Article Authors', $this->article->getAuthors());
        $this->assertSame(1, $this->article->getStartPage());
        $this->assertSame(10, $this->article->getEndPage());
        $this->assertSame(123456, $this->article->getMuseId());
        $this->assertSame('doi', $this->article->getDoi());
        $this->assertSame('123456.pdf', $this->article->getFilename());
    }
}
