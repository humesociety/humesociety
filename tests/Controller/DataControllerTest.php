<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DataControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testCurrentUser()
    {
        $this->client->request('GET', '/data/user');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUsers()
    {
        $this->client->request('GET', '/data/users');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testMembers()
    {
        $this->client->request('GET', '/data/members');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testIssues()
    {
        $this->client->request('GET', '/data/issues');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testConference()
    {
        $this->client->request('GET', '/data/conference');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testConferenceKeywords()
    {
        $this->client->request('GET', '/data/conference/keywords');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
