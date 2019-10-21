<?php

namespace App\Tests\Entity\User;

use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the user entity.
 */
class UserTest extends WebTestCase
{
    private $today;
    private $user;

    public function setUp()
    {
        $this->today = new \DateTime('today');
        $this->user = new User();
    }

    public function testConstructor()
    {
        $this->assertSame('uninitialised user', (string) $this->user);
        $this->assertSame(null, $this->user->getId());
        $this->assertSame(null, $this->user->getUsername());
        $this->assertSame(null, $this->user->getEmail());
        $this->assertSame(['ROLE_USER'], $this->user->getRoles());
        $this->assertSame(null, $this->user->getPassword());
        $this->assertEquals($this->today, $this->user->getDateJoined());
        $this->assertSame(false, $this->user->getRejoined());
        $this->assertSame(null, $this->user->getLastLogin());
        $this->assertSame(
            'Doctrine\Common\Collections\ArrayCollection',
            get_class($this->user->getCandidacies())
        );
        $this->assertSame(false, $this->user->getVoted());
        $this->assertSame(null, $this->user->getNotes());
        $this->assertSame(null, $this->user->getDues());
        $this->assertSame(false, $this->user->getLifetimeMember());
        $this->assertSame(null, $this->user->getPasswordResetSecret());
        $this->assertSame(null, $this->user->getPasswordResetSecretExpires());
        $this->assertSame(null, $this->user->getFirstname());
        $this->assertSame(null, $this->user->getLastname());
        $this->assertSame(null, $this->user->getDepartment());
        $this->assertSame(null, $this->user->getInstitution());
        $this->assertSame(null, $this->user->getCity());
        $this->assertSame(null, $this->user->getState());
        $this->assertSame('USA', $this->user->getCountry());
        $this->assertSame(null, $this->user->getOfficePhone());
        $this->assertSame(null, $this->user->getHomePhone());
        $this->assertSame(null, $this->user->getFax());
        $this->assertSame(null, $this->user->getWebpage());
        $this->assertSame(true, $this->user->getReceiveEmail());
        $this->assertSame(true, $this->user->getReceiveHumeStudies());
        $this->assertSame(null, $this->user->getMailingAddress());
        $this->assertSame(
            'Doctrine\Common\Collections\ArrayCollection',
            get_class($this->user->getSubmissions())
        );
        $this->assertSame(false, $this->user->isWillingToReview());
        $this->assertSame(false, $this->user->isWillingToComment());
        $this->assertSame(false, $this->user->isWillingToChair());
        $this->assertSame(null, $this->user->getKeywords());
        $this->assertSame(false, $this->user->isMember());
        $this->assertSame(false, $this->user->isMemberInGoodStanding());
        $this->assertSame(false, $this->user->isMemberInArrears());
    }
}
