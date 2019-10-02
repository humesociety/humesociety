<?php

namespace App\Entity\DuesPayment;

use Doctrine\ORM\EntityManagerInterface;

/**
 * The dues payment handler contains the main business logic for reading and writing dues payment data.
 */
class DuesPaymentHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The dues payment repository.
     *
     * @var DuesPaymentRepository
     */
    private $repository;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(DuesPayment::class);
    }

    /**
     * Get all dues payments.
     *
     * @return DuesPayment[]
     */
    public function getDuesPayments() : Array
    {
        return $this->repository->findAll();
    }

    /**
     * Get a dues payment by its PayPal order ID.
     *
     * @return DuesPayment|null
     */
    public function getDuesPaymentByPaypalOrderId(string $orderId) : ?DuesPayment
    {
        return $this->repository->findOneByPaypalOrderId($orderId);
    }

    /**
     * Save a dues payment to the database.
     *
     * @param DuesPayment The dues payment to save.
     * @return void
     */
    public function saveDuesPayment(DuesPayment $duesPayment)
    {
        $this->manager->persist($duesPayment);
        $this->manager->flush();
    }
}
