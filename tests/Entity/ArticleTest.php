<?php

namespace App\Tests\Entity;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleHandler;
use App\Entity\Issue\IssueHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the article entity and related services.
 */
class ArticleTest extends WebTestCase
{
    private $articleHandler;
    private $issueHandler;

    public function __construct(ArticleHandler $articleHandler, IssueHandler $issueHandler)
    {
        $this->articleHandler = $articleHandler;
        $this->issueHandler = $issueHandler;
    }

    public function testGettersAndSetters()
    {
        $issue = $issueHandler->getIssue(1, 1);
    }
}
