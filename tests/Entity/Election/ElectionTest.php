<?php

namespace App\Tests\Entity\Election;

use App\Entity\Election\Election;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the election entity.
 */
class ElectionTest extends WebTestCase
{
    private $nextYear;
    private $election;

    public function setUp()
    {
        $this->nextYear = idate('Y') + 1;
        $this->election = new Election();
    }

    public function testConstructor()
    {
        $this->assertSame((string) $this->nextYear, (string) $this->election);
        $this->assertSame(null, $this->election->getId());
        $this->assertSame($this->nextYear, $this->election->getYear());
        $this->assertSame(false, $this->election->getOpen());
        $this->assertSame(0, $this->election->getVotes());
        $this->assertSame(0, $this->election->getPopulation());
    }
}
