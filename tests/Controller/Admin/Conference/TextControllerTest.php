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
        $this->client->request('GET', '/admin/conference/text');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit()
    {
        $this->client->request('GET', '/admin/conference/text/edit/submission-guidance');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/review-guidance');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/review-acknowledgement');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/comment-guidance');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/comment-acknowledgement');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/chair-guidance');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/chair-acknowledgement');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/paper-guidance');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/text/edit/paper-acknowledgement');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
