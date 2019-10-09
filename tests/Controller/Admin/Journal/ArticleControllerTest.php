<?php

namespace App\Tests\Controller\Admin\Journal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'editor',
            'PHP_AUTH_PW' => 'password'
        ]);
    }
}
