<?php

namespace App\Tests\Controller\Admin\Content;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImageControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'evpt',
            'PHP_AUTH_PW' => 'password'
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/admin/content/image/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
