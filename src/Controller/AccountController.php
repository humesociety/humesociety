<?php

namespace App\Controller;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\DuesPayment\DuesPayment;
use App\Entity\DuesPayment\DuesPaymentHandler;
use App\Entity\Email\EmailHandler;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionHandler;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use App\Entity\User\UserDetailsType;
use App\Entity\User\UserChangePasswordType;
use App\Entity\User\UserAvailabilityType;
use App\Entity\User\UserSettingsType;
use App\Entity\User\UserVolunteerType;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/account", name="account_")
 *
 * This controller contains functions for users to manage their accounts.
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/{tab}", name="index", requirements={"tab": "details|settings"})
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, UserHandler $userHandler, $tab = 'details'): Response
    {
        if (!$this->getUser()->isMember()) {
            return $this->redirectToRoute('account_pay');
        }

        // contact details form
        $detailsForm = $this->createForm(UserDetailsType::class, $this->getUser());
        $detailsForm->handleRequest($request);

        if ($detailsForm->isSubmitted() && $detailsForm->isValid()) {
            $userHandler->saveUser($this->getUser());
            $this->addFlash('success', 'Your contact details have been updated.');
        }

        // membership settings form
        $settingsForm = $this->createForm(UserSettingsType::class, $this->getUser());
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $userHandler->saveUser($this->getUser());
            $this->addFlash('success', 'Your membership settings have been updated.');
            $tab = 'settings';
        }

        return $this->render('site/account/index.twig', [
            'page' => ['id' => 'details', 'section' => 'account'],
            'tab' => $tab,
            'userDetailsForm' => $detailsForm->createView(),
            'userSettingsForm' => $settingsForm->createView()
        ]);
    }

    /**
     * @Route("/research/{tab}", name="research", requirements={"tab": "availability|submissions|reviews"})
     * @IsGranted("ROLE_USER")
     */
    public function research(
        Request $request,
        ConferenceHandler $conferenceHandler,
        UserHandler $userHandler,
        $tab = 'availability'
    ): Response {
        // research availability form
        $availabiliytForm = $this->createForm(UserAvailabilityType::class, $this->getUser());
        $availabiliytForm->handleRequest($request);

        if ($availabiliytForm->isSubmitted() && $availabiliytForm->isValid()) {
            $userHandler->saveUser($this->getUser());
            $this->addFlash('success', 'Your availability has been updated.');
        }

        return $this->render('site/account/research.twig', [
            'page' => ['id' => 'research', 'section' => 'account'],
            'tab' => $tab,
            'conference' => $conferenceHandler->getCurrentConference(),
            'today' => new \DateTime('today'),
            'userAvailabilityForm' => $availabiliytForm->createView()
        ]);
    }

    /**
     * @Route("/password", name="password")
     * @IsGranted("ROLE_USER")
     */
    public function password(
        Request $request,
        UserHandler $userHandler,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $form = $this->createForm(UserChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('new_password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($this->getUser(), $plainPassword);
            $this->getUser()->setPassword($encodedPassword);
            $userHandler->saveUser($this->getUser());
            $this->addFlash('success', 'Your password has been changed.');
        }

        return $this->render('site/account/password.twig', [
            'page' => ['id' => 'password', 'section' => 'account'],
            'userChangePasswordForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/pay", name="pay")
     * @IsGranted("ROLE_USER")
     */
    public function pay(Request $request): Response
    {
        if ($this->getUser()->getLifetimeMember() === true) {
            $payable = false;
        } elseif ($this->getUser()->isMember()) {
            $twoMonthsAway = new \DateTime('+2 months');
            $payable = $twoMonthsAway >= $this->getUser()->getDues();
        } else {
            $payable = true;
        }
        return $this->render('site/account/pay.twig', [
            'page' => ['id' => 'pay', 'section' => 'account'],
            'payable' => $payable,
            'dev' => ($_ENV['APP_ENV'] === 'dev')
        ]);
    }

    /**
     * @Route("/paid/{orderId}", name="paid")
     * @IsGranted("ROLE_USER")
     */
    public function paid(
        string $orderId,
        Request $request,
        DuesPaymentHandler $duesPaymentHandler,
        EmailHandler $emailHandler,
        UserHandler $userHandler
    ): Response {
        if ($_ENV['APP_ENV'] === 'dev') {
            $clientId = $_ENV['PAYPAL_SANDBOX_CLIENT_ID'];
            $secret = $_ENV['PAYPAL_SANDBOX_SECRET'];
            $environment = new SandboxEnvironment($clientId, $secret);
        } else {
            $clientId = $_ENV['PAYPAL_CLIENT_ID'];
            $secret = $_ENV['PAYPAL_SECRET'];
            $environment = new ProductionEnvironment($clientId, $secret);
        }

        $client = new PayPalHttpClient($environment);
        $response = $client->execute(new OrdersGetRequest($orderId));
        $result = $response->result;

        if ($result->status === 'COMPLETED') {
            $duesPayment = $duesPaymentHandler->getDuesPaymentByPaypalOrderId($orderId);
            if (!$duesPayment) {
                $newMember = ($this->getUser()->getDues() == null);
                $duesPayment = new DuesPayment();
                $duesPayment->setPaypalOrderId($orderId);
                $duesPayment->setUser($this->getUser());
                $duesPayment->setAmount($result->purchase_units[0]->amount->value);
                $duesPayment->setDescription($result->purchase_units[0]->description);
                $duesPaymentHandler->saveDuesPayment($duesPayment);
                $userHandler->updateDues($this->getUser(), $duesPayment);
                if ($newMember) {
                    $emailHandler->sendNewMemberEmail($this->getUser());
                }
            }
        }

        return $this->render('site/account/paid.twig', [
            'page' => ['id' => 'pay', 'section' => 'account'],
            'orderId' => $orderId,
            'result' => $result,
            'duesPayment' => $duesPayment
        ]);
    }
}
