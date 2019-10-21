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
        $this->client->request('GET', '/admin/conference/email');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit()
    {
        $this->client->request('GET', '/admin/conference/email/edit/submission-acknowledgement');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/submission-acceptance');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/submission-rejection');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/submission-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/submission-comments-submitted');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/review-invitation');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/review-invitation-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/review-submission-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/comment-invitation');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/comment-invitation-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/comment-paper-submitted');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/comment-submission-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/chair-invitation');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/chair-invitation-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/paper-invitation');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/conference/email/edit/paper-invitation-reminder');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
