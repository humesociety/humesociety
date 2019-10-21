<?php

namespace App\Tests\Entity\DuesPayment;

use App\Entity\DuesPayment\DuesPayment;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the dues payment entity.
 */
class DuesPaymentTest extends WebTestCase
{
    private $today;
    private $user;
    private $duesPayment;

    public function setUp()
    {
        $this->today = new \DateTime('today');
        $this->user = new User();
        $this->duesPayment = new DuesPayment($this->user, 'abcdefg');
    }

    public function testConstructor()
    {
        $this->assertSame('abcdefg', (string) $this->duesPayment);
        $this->assertSame(null, $this->duesPayment->getId());
        $this->assertSame('abcdefg', $this->duesPayment->getPaypalOrderId());
        $this->assertSame($this->user, $this->duesPayment->getUser());
        $this->assertEquals($this->today, $this->duesPayment->getDate());
        $this->assertSame(null, $this->duesPayment->getAmount());
        $this->assertSame(null, $this->duesPayment->getDescription());
    }
}
