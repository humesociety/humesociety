<?php

namespace App\Tests\Entity\Candidate;

use App\Entity\Candidate\Candidate;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the candidate entity.
 */
class CandidateTest extends WebTestCase
{
    private $candidate;

    public function setUp()
    {
        $this->candidate = new Candidate();
    }

    public function testConstructor()
    {
        $this->assertSame('uninitialised candidate', (string) $this->candidate);
        $this->assertSame(null, $this->candidate->getId());
        $this->assertSame(null, $this->candidate->getFirstname());
        $this->assertSame(null, $this->candidate->getLastname());
        $this->assertSame(null, $this->candidate->getInstitution());
        $this->assertSame(null, $this->candidate->getUser());
        $this->assertSame(idate('Y') + 1, $this->candidate->getStart());
        $this->assertSame(idate('Y') + 3, $this->candidate->getEnd());
        $this->assertSame(null, $this->candidate->getDescription());
        $this->assertSame(0, $this->candidate->getVotes());
        $this->assertSame(false, $this->candidate->getElected());
        $this->assertSame(true, $this->candidate->getReelectable());
        $this->assertSame(false, $this->candidate->getPresident());
        $this->assertSame(false, $this->candidate->getEvpt());
    }

    public function testGettersAndSetters()
    {
        $user = new User();
        $this->candidate->setFirstname('firstname')
            ->setLastname('lastname')
            ->setInstitution('institution')
            ->setUser($user)
            ->setStart(2000)
            ->setEnd(2002)
            ->setDescription('description')
            ->setVotes(10)
            ->setElected(true)
            ->setReelectable(false)
            ->setPresident(true)
            ->setEvpt(true);
        $this->assertSame('firstname lastname (2000-2002)', (string) $this->candidate);
        $this->assertSame('firstname', $this->candidate->getFirstname());
        $this->assertSame('lastname', $this->candidate->getLastname());
        $this->assertSame('institution', $this->candidate->getInstitution());
        $this->assertSame($user, $this->candidate->getUser());
        $this->assertSame(2000, $this->candidate->getStart());
        $this->assertSame(2002, $this->candidate->getEnd());
        $this->assertSame('description', $this->candidate->getDescription());
        $this->assertSame(10, $this->candidate->getVotes());
        $this->assertSame(true, $this->candidate->getElected());
        $this->assertSame(false, $this->candidate->getReelectable());
        $this->assertSame(true, $this->candidate->getPresident());
        $this->assertSame(true, $this->candidate->getEvpt());
    }
}
