<?php

namespace App\Tests\Entity\Conference;

use App\Entity\Chair\Chair;
use App\Entity\Comment\Comment;
use App\Entity\Conference\Conference;
use App\Entity\Paper\Paper;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests for the conference handler.
 *
 * Also includes tests for the chair, comment, paper, review, and submission handlers, because these
 * all need conferences to work.
 */
class ConferenceHandlerTest extends KernelTestCase
{
    private $chairs;
    private $comments;
    private $conferences;
    private $papers;
    private $reviews;
    private $submissions;
    private $users;

    public function setUp()
    {
        self::bootKernel();
        $this->chairs = self::$container->get('App\Entity\Chair\ChairHandler');
        $this->comments = self::$container->get('App\Entity\Comment\CommentHandler');
        $this->conferences = self::$container->get('App\Entity\Conference\ConferenceHandler');
        $this->papers = self::$container->get('App\Entity\Paper\PaperHandler');
        $this->reviews = self::$container->get('App\Entity\Review\ReviewHandler');
        $this->submissions = self::$container->get('App\Entity\Submission\SubmissionHandler');
        $this->users = self::$container->get('App\Entity\User\UserHandler');
    }

    public function testGetConferences()
    {
        $this->assertSame(1, sizeof($this->conferences->getConferences()));
    }

    public function testGetCurrentConference()
    {
        $conference = $this->conferences->getCurrentConference();
        $nextYear = idate('Y') + 1;
        $this->assertSame(1, $conference->getNumber());
        $this->assertSame($nextYear, $conference->getYear());
        $this->assertSame('Oxford', $conference->getTown());
        $this->assertSame('GBR', $conference->getCountry());
        $this->assertSame('University of Oxford', $conference->getInstitution());
    }
}
