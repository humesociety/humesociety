<?php

namespace App\Tests\Entity\Email;

use App\Entity\Email\Email;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Tests for the email entity.
 */
class EmailTest extends WebTestCase
{
    private $email;
    private $pdf;

    public function setUp()
    {
        self::bootKernel();
        $uploadsDir = self::$container->getParameter('uploads_directory');
        $this->email = new Email();
        $this->user = new User();
        $this->pdf = new UploadedFile($uploadsDir.'test_pdf.pdf', 'test_pdf.pdf');
    }

    public function testConstructor()
    {
        $this->assertSame('uninitialised email', (string) $this->email);
        $this->assertSame(null, $this->email->getSender());
        $this->assertSame(null, $this->email->getRecipient());
        $this->assertSame(null, $this->email->getSubject());
        $this->assertSame(null, $this->email->getAttachment());
        $this->assertSame('array', gettype($this->email->getTwigs()));
        $this->assertTrue(sizeof($this->email->getTwigs()) === 1);
        $this->assertSame(null, $this->email->getTwigs()['content']);
        $this->assertSame('base', $this->email->getTemplate());
    }

    public function testGettersAndSetters()
    {
        $this->email->setSender('sender')
            ->setRecipient($this->user)
            ->setSubject('subject')
            ->setAttachment($this->pdf)
            ->addTwig('foo', 'bar')
            ->setContent('content')
            ->setTemplate('template');
        $this->assertSame('subject', (string) $this->email);
        $this->assertSame('sender', $this->email->getSender());
        $this->assertSame($this->user, $this->email->getRecipient());
        $this->assertSame('subject', $this->email->getSubject());
        $this->assertSame($this->pdf, $this->email->getAttachment());
        $this->assertSame('bar', $this->email->getTwigs()['foo']);
        $this->assertSame('content', $this->email->getContent());
        $this->assertSame('template', $this->email->getTemplate());
    }

    public function testPrepareRecipientContent()
    {
        $this->user->setUsername('a')
            ->setEmail('b')
            ->setFirstname('c')
            ->setLastname('d');
        $this->email->setRecipient($this->user)
            ->setContent('{{ username }} {{ email }} {{ firstname }} {{ lastname }}');
        $this->email->prepareRecipientContent();
        $this->assertSame('a b c d', $this->email->getContent());
    }
}
