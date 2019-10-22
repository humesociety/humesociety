<?php

namespace App\Tests\Entity\NewsItem;

use App\Entity\NewsItem\NewsItem;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the news item entity.
 */
class NewsItemTest extends WebTestCase
{
    private $today;
    private $fourMonthsAway;
    private $newsItem;

    public function setUp()
    {
        $this->today = new \DateTime('today');
        $this->fourMonthsAway = new \DateTime('today');
        $this->fourMonthsAway->add(new \DateInterval('P4M'));
        $this->newsItem = new NewsItem('society');
    }

    public function testConstructor()
    {
        $this->assertSame(null, $this->newsItem->getId());
        $this->assertSame('society', $this->newsItem->getCategory());
        $this->assertSame(null, $this->newsItem->getTitle());
        $this->assertEquals($this->today, $this->newsItem->getDate());
        $this->assertEquals($this->fourMonthsAway, $this->newsItem->getEnd());
        $this->assertSame(null, $this->newsItem->getContent());
    }
}
