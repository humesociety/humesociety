<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTemplate()
    {
        $client = static::createClient();

        $client->request('GET', '/template/default');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/society-governance');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/conferences-forthcoming');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/conferences-all');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/hs-issues-archive');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/parallel-references');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/news-members');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/news-conferences');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/news-fellowships');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/news-archived');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/membership-stats');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/membership-list');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/minutes-reports');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/template/committee-voting');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}