<?php

namespace App\Tests\Controller\Admin\Journal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailingControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'editor',
            'PHP_AUTH_PW' => 'password'
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/admin/journal/mailing/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
