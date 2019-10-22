<?php

namespace App\Tests\Entity\Page;

use App\Entity\Page\Page;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the page entity.
 */
class PageTest extends WebTestCase
{
    private $page;

    public function setUp()
    {
        $this->page = new Page();
    }

    public function testConstructor()
    {
        $this->assertSame(null, $this->page->getId());
        $this->assertSame(null, $this->page->getSection());
        $this->assertSame(null, $this->page->getPosition());
        $this->assertSame(null, $this->page->getSlug());
        $this->assertSame(null, $this->page->getTitle());
        $this->assertSame(null, $this->page->getTemplate());
        $this->assertSame(null, $this->page->getContent());
    }
}
