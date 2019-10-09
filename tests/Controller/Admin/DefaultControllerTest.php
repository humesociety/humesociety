<?php

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private $client;
    private $memberClient;
    private $evptClient;
    private $organiserClient;
    private $editorClient;
    private $techClient;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->memberClient = static::createClient([], [
            'PHP_AUTH_USER' => 'member',
            'PHP_AUTH_PW' => 'password'
        ]);
        $this->evptClient = static::createClient([], [
            'PHP_AUTH_USER' => 'evpt',
            'PHP_AUTH_PW' => 'password'
        ]);
        $this->organiserClient = static::createClient([], [
            'PHP_AUTH_USER' => 'organiser',
            'PHP_AUTH_PW' => 'password'
        ]);
        $this->editorClient = static::createClient([], [
            'PHP_AUTH_USER' => 'editor',
            'PHP_AUTH_PW' => 'password'
        ]);
        $this->techClient = static::createClient([], [
            'PHP_AUTH_USER' => 'tech',
            'PHP_AUTH_PW' => 'password'
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/admin');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->memberClient->request('GET', '/admin');
        $this->assertEquals(403, $this->memberClient->getResponse()->getStatusCode());

        $this->evptClient->request('GET', '/admin');
        $this->assertEquals(301, $this->evptClient->getResponse()->getStatusCode());

        $this->organiserClient->request('GET', '/admin');
        $this->assertEquals(301, $this->organiserClient->getResponse()->getStatusCode());

        $this->editorClient->request('GET', '/admin');
        $this->assertEquals(301, $this->editorClient->getResponse()->getStatusCode());

        $this->techClient->request('GET', '/admin');
        $this->assertEquals(301, $this->techClient->getResponse()->getStatusCode());
    }
}
