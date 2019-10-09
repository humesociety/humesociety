<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testIndex()
    {
        $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testTemplate()
    {
        $this->client->request('GET', '/template/default');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/society-governance');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/conferences-forthcoming');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/conferences-all');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/hs-issues-archive');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/parallel-references');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-members');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-conferences');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-fellowships');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-jobs');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-archived');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/membership-stats');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/membership-list');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/minutes-reports');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/committee-voting');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
