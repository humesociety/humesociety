<?php

namespace App\Tests\Entity\User;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests for the user handler.
 */
class UserHandlerTest extends KernelTestCase
{
    private $users;

    public function setUp()
    {
        self::bootKernel();
        $this->users = self::$container->get('App\Entity\User\UserHandler');
    }

    public function testGetUsers()
    {
        $this->assertSame(7, sizeof($this->users->getUsers()));
    }
}
