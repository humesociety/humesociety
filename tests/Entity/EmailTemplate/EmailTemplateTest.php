<?php

namespace App\Tests\Entity\EmailTemplate;

use App\Entity\EmailTemplate\EmailTemplate;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the email template entity.
 */
class EmailTemplateTest extends WebTestCase
{
    private $emailTemplate;

    public function setUp()
    {
        $this->emailTemplate = new EmailTemplate();
    }

    public function testConstructor()
    {
        $this->assertSame(null, $this->emailTemplate->getId());
        $this->assertSame(null, $this->emailTemplate->getLabel());
        $this->assertSame(null, $this->emailTemplate->getGroup());
        $this->assertSame(null, $this->emailTemplate->getTitle());
        $this->assertSame(null, $this->emailTemplate->getDescription());
        $this->assertSame(null, $this->emailTemplate->getSender());
        $this->assertSame(null, $this->emailTemplate->getSubject());
        $this->assertSame(null, $this->emailTemplate->getContent());
    }
}
