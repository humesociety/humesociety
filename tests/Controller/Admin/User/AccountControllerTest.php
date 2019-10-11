<?php

namespace App\Tests\Controller\Admin\User;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'tech',
            'PHP_AUTH_PW' => 'password'
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/admin/user/account/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
