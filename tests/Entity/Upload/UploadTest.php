<?php

namespace App\Tests\Entity\Upload;

use App\Entity\Upload\Upload;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the upload entity.
 */
class UploadTest extends WebTestCase
{
    private $upload;

    public function setUp()
    {
        $this->upload = new Upload();
    }

    public function testConstructor()
    {
        $this->assertSame(null, $this->upload->getPath());
        $this->assertSame(null, $this->upload->getFilename());
    }
}
