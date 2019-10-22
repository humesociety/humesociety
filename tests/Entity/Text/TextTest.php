<?php

namespace App\Tests\Entity\Text;

use App\Entity\Text\Text;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the text entity.
 */
class TextTest extends WebTestCase
{
    private $text;

    public function setUp()
    {
        $this->text = new Text('label');
    }

    public function testConstructor()
    {
        $this->assertSame(null, $this->text->getId());
        $this->assertSame('label', $this->text->getLabel());
        $this->assertSame(null, $this->text->getContent());
        $this->assertSame(null, $this->text->getTitle());
        $this->assertSame(null, $this->text->getDescription());
    }
}
