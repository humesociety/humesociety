<?php

namespace App\Tests\Controller\Admin\Journal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IssueControllerTest extends WebTestCase
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
        $this->client->request('GET', '/admin/journal/issue');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
