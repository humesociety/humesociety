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
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testTemplate()
    {
        $this->client->request('GET', '/template/default');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/society-governance');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/conferences-forthcoming');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/conferences-all');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/hs-issues-archive');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/parallel-references');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-members');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-conferences');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-fellowships');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-jobs');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/news-archived');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/membership-stats');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/membership-list');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/minutes-reports');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/template/committee-voting');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
