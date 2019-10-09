<?php

namespace App\Tests\Controller\Admin\Conference;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmailControllerTest extends WebTestCase
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
        $this->client->request('GET', '/admin/conference/email/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit()
    {
        $this->client->request('GET', '/admin/conference/email/edit/submission');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/review');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/pending-reminder');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/accepted-reminder');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/accept');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/reject');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
