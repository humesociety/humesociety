<?php

namespace App\Tests\Entity\Submission;

use App\Entity\Conference\Conference;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the submission entity.
 */
class SubmissionTest extends WebTestCase
{
    private $today;
    private $conference;
    private $submission;
    private $user;

    public function setUp()
    {
        $this->today = new \DateTime('today');
        $this->conference = new Conference();
        $this->user = new User();
        $this->submission = new Submission($this->user, $this->conference);
    }

    public function testConstructor()
    {
        $this->assertSame(null, $this->submission->getId());
        $this->assertSame($this->user, $this->submission->getUser());
        $this->assertSame($this->conference, $this->submission->getConference());
    }
}
