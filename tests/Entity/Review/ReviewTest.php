<?php

namespace App\Tests\Entity\Review;

use App\Entity\Conference\Conference;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the review entity.
 */
class ReviewTest extends WebTestCase
{
    private $conference;
    private $review;
    private $submission;
    private $user;

    public function setUp()
    {
        $this->conference = new Conference();
        $this->user = new User();
        $this->submission = new Submission($this->user, $this->conference);
        $this->review = new Review($this->submission);
    }

    public function testGettersAndSetters()
    {
        $this->assertSame(null, $this->review->getId());
        $this->assertSame($this->submission, $this->review->getSubmission());
        $this->assertSame(null, $this->review->getUser());
        $this->assertSame(null, $this->review->getGrade());
        $this->assertSame(null, $this->review->getComments());
    }
}
