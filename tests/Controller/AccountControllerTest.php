<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'member',
            'PHP_AUTH_PW' => 'password'
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/account');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testPassword()
    {
        $this->client->request('GET', '/account/password');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testResearch()
    {
        $this->client->request('GET', '/account/research');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testPay()
    {
        $this->client->request('GET', '/account/pay');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
