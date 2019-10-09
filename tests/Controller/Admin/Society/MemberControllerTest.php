<?php

namespace App\Tests\Controller\Admin\Society;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MemberControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'evpt',
            'PHP_AUTH_PW' => 'password'
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/admin/society/member/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
