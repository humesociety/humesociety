<?php

namespace App\Tests\Entity\Conference;

use App\Entity\Conference\Conference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the conference entity.
 */
class ConferenceTest extends WebTestCase
{
    private $conference;

    public function setUp()
    {
        $this->conference = new Conference();
    }

    public function testConstructor()
    {
        $this->assertSame('uninitialised conference', (string) $this->conference);
        $this->assertSame(null, $this->conference->getId());
        $this->assertSame(null, $this->conference->getNumber());
        $this->assertSame(null, $this->conference->getOrdinal());
        $this->assertSame(null, $this->conference->getYear());
        $this->assertSame(null, $this->conference->getStartDate());
        $this->assertSame(null, $this->conference->getEndDate());
        $this->assertSame(null, $this->conference->getInstitution());
        $this->assertSame(null, $this->conference->getTown());
        $this->assertSame('USA', $this->conference->getCountry());
        $this->assertSame(null, $this->conference->getWebsite());
        $this->assertSame(null, $this->conference->getDeadline());
        $this->assertSame(
            'Doctrine\Common\Collections\ArrayCollection',
            get_class($this->conference->getSubmissions())
        );
        $this->assertSame('array', gettype($this->conference->getUploads()));
    }
}
