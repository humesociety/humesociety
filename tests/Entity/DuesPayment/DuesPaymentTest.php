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
    private $now;
    private $user;
    private $duesPayment;

    public function setUp()
    {
        $this->now = new \DateTime();
        $this->user = new User();
        $this->duesPayment = new DuesPayment($this->user, 'abcdefg');
    }

    public function testConstructor()
    {
        $this->assertSame('abcdefg', (string) $this->duesPayment);
        $this->assertSame(null, $this->duesPayment->getId());
        $this->assertSame('abcdefg', $this->duesPayment->getPaypalOrderId());
        $this->assertSame($this->user, $this->duesPayment->getUser());
        $this->assertTrue((int) date_diff($this->now, $this->duesPayment->getDate())->format('%s%') < 1);
        $this->assertSame(null, $this->duesPayment->getAmount());
        $this->assertSame(null, $this->duesPayment->getDescription());
    }
}
