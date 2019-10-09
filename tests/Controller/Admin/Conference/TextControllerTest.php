<?php

namespace App\Tests\Controller\Admin\Conference;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TextControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'organiser',
            'PHP_AUTH_PW' => 'password'
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/admin/conference/text/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit()
    {
        $this->client->request('GET', '/admin/conference/text/edit/submission');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/review');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/thanks');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
