<?php

namespace App\Entity\DuesPayment;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

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
     * @var EntityRepository
     */
    private $repository;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface $manager The Doctrine entity manager.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(DuesPayment::class);
    }

    /**
     * Fetch order details from PayPal.
     *
     * @param string $orderId The PayPal order ID.
     * @return object
     */
    public function fetchOrderFromPayPal(string $orderId): object
    {
        // create the PayPal environment
        if ($_ENV['APP_ENV'] === 'prod') {
            $environment = new ProductionEnvironment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SECRET']);
        } else {
            $environment = new SandboxEnvironment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SECRET']);
        }
        // create the PayPal client and check the order status
        $client = new PayPalHttpClient($environment);
        $response = $client->execute(new OrdersGetRequest($orderId));
        // return the response result
        return $response->result;
    }

    /**
     * Create a dues payment based on a PayPal order response.
     *
     * @param User $user The user who made the payment.
     * @param string $orderId The PayPal order ID.
     * @param Object $order The PayPal order response.
     * @throws \Exception
     * @return DuesPayment
     */
    public function createDuesPaymentFromOrder(User $user, string $orderId, $order): DuesPayment
    {
        $duesPayment = new DuesPayment($user, $orderId);
        $duesPayment->setAmount($order->purchase_units[0]->amount->value);
        $duesPayment->setDescription($order->purchase_units[0]->description);
        return $duesPayment;
    }

    /**
     * Get a dues payment by its PayPal order ID.
     *
     * @param string $orderId The PayPal order ID.
     * @return DuesPayment|null
     */
    public function getDuesPaymentByPaypalOrderId(string $orderId) : ?DuesPayment
    {
        return $this->repository->findOneByPaypalOrderId($orderId);
    }

    /**
     * Save a dues payment to the database.
     *
     * @param DuesPayment $duesPayment The dues payment to save.
     * @return void
     */
    public function saveDuesPayment(DuesPayment $duesPayment)
    {
        $this->manager->persist($duesPayment);
        $this->manager->flush();
    }
}
