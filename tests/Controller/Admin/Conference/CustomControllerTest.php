<?php

namespace App\Tests\Controller\Admin\Conference;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomControllerTest extends WebTestCase
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
        $this->client->request('GET', '/admin/conference/custom');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testText()
    {
        $this->client->request('GET', '/admin/conference/custom/text/submission');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/custom/text/review');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/custom/text/thanks');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEmail()
    {
        $this->client->request('GET', '/admin/conference/custom/email/submission');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/custom/email/review');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/custom/email/pending-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/custom/email/accepted-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/custom/email/accept');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/custom/email/reject');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
