<?php

namespace App\Entity\DuesPayment;

use Doctrine\ORM\EntityManagerInterface;

class DuesPaymentHandler
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(DuesPayment::class);
    }

    public function getDuesPayments() : Array
    {
        return $this->repository->findAll();
    }

    public function getDuesPaymentByPaypalOrderId(string $orderId) : ?DuesPayment
    {
        return $this->repository->findOneByPaypalOrderId($orderId);
    }

    public function saveDuesPayment(DuesPayment $duesPayment)
    {
        $this->manager->persist($duesPayment);
        $this->manager->flush();
    }
}
