<?php

namespace App\Tests\Entity\Issue;

use App\Entity\Issue\Issue;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the issue entity.
 */
class IssueTest extends WebTestCase
{
    private $issue;

    public function setUp()
    {
        $this->issue = new Issue();
    }

    public function testConstructor()
    {
        $this->assertSame(null, $this->issue->getId());
        $this->assertSame(null, $this->issue->getVolume());
        $this->assertSame(null, $this->issue->getYear());
        $this->assertSame(null, $this->issue->getDecade());
        $this->assertSame(null, $this->issue->getNumber());
        $this->assertSame(null, $this->issue->getMonth());
        $this->assertSame(null, $this->issue->getName());
        $this->assertSame(null, $this->issue->getMuseId());
        $this->assertSame(null, $this->issue->getEditors());
        $this->assertSame('Doctrine\Common\Collections\ArrayCollection', get_class($this->issue->getArticles()));
    }
}
