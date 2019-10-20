<?php

namespace App\Controller;

use App\Entity\DuesPayment\DuesPayment;
use App\Entity\DuesPayment\DuesPaymentHandler;
use App\Entity\Email\EmailHandler;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use App\Entity\User\UserTypeDetails;
use App\Entity\User\UserTypeChangePassword;
use App\Entity\User\UserTypeSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller for the user account area of the site.
 *
 * @Route("/account", name="account_")
 */
class AccountController extends AbstractController
{
    /**
     * Route for editing basic account details.
     *
     * @param Request Symfony's request object.
     * @param UserHandler The user handler.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/{tab}", name="index", requirements={"tab": "details|settings"})
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, UserHandler $users, string $tab = 'details'): Response
    {
        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'details', 'section' => 'account', 'title' => 'Personal Information'],
            'tab' => $tab
        ];

        // create and handle the contact details form
        $detailsForm = $this->createForm(UserTypeDetails::class, $this->getUser());
        $detailsForm->handleRequest($request);
        if ($detailsForm->isSubmitted() && $detailsForm->isValid()) {
            $users->saveUser($this->getUser());
            $this->addFlash('success', 'Your contact details have been updated.');
        }

        // create and handle the membership settings form
        $settingsForm = $this->createForm(UserTypeSettings::class, $this->getUser());
        $settingsForm->handleRequest($request);
        if ($settingsForm->isSubmitted()) {
            $tab = 'settings';
            if ($settingsForm->isValid()) {
                $users->saveUser($this->getUser());
                $this->addFlash('success', 'Your membership settings have been updated.');
            }
        }

        // add additional twig variables
        $twigs['detailsForm'] = $detailsForm->createView();
        $twigs['settingsForm'] = $settingsForm->createView();

        // render and return the page
        return $this->render('site/account/index.twig', $twigs);
    }

    /**
     * Route for managing conference submissions, reviews, etc.
     *
     * @return Response
     * @Route("/research", name="research")
     * @IsGranted("ROLE_USER")
     */
    public function research(): Response
    {
        // redirect to the research avaiability page
        return $this->redirectToRoute('account_research_availability');
    }

    /**
     * Route for paying dues.
     *
     * @param Request Symfony's request object.
     * @return Response
     * @Route("/pay", name="pay")
     * @IsGranted("ROLE_USER")
     */
    public function pay(Request $request): Response
    {
        // determine whether the user can (and needs to) pay their dues
        if ($this->getUser()->getLifetimeMember() === true) {
            $payable = false;
        } elseif ($this->getUser()->isMember()) {
            $twoMonthsAway = new \DateTime('+2 months');
            $payable = $twoMonthsAway >= $this->getUser()->getDues();
        } else {
            $payable = true;
        }

        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'pay', 'section' => 'account', 'title' => 'Pay Dues'],
            'payable' => $payable
        ];

        // render and return the page
        return $this->render('site/account/pay.twig', $twigs);
    }

    /**
     * Route PayPal sends the user to after payment is completed.
     *
     * @param Request Symfony's request object.
     * @param DuesPaymentHandler The dues payment handler.
     * @param EmailHandler The email handler.
     * @param UserHandler The user handler.
     * @param string The PayPal order id.
     * @Route("/paid/{orderId}", name="paid")
     * @IsGranted("ROLE_USER")
     */
    public function paid(
        Request $request,
        DuesPaymentHandler $duesPayments,
        EmailHandler $emails,
        UserHandler $users,
        string $orderId
    ): Response {
        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'pay', 'section' => 'account', 'title' => 'Pay Dues'],
            'orderId' => $orderId
        ];

        // fetch the details of the order from PayPal
        $order = $duesPayments->fetchOrderFromPaypal($orderId);

        // if the payment is complete, update the database
        if ($order->status === 'COMPLETED') {
            $twigs['completed'] = true;
            $duesPayment = $duesPayments->getDuesPaymentByPaypalOrderId($orderId);
            if (!$duesPayment) {
                $newMember = ($this->getUser()->getDues() === null);
                $duesPayment = $duesPayments->createFromOrder($this->getUser(), $oderId, $order);
                $duesPayments->saveDuesPayment($duesPayment);
                $users->updateDues($this->getUser(), $duesPayment);
                if ($newMember) {
                    $emails->sendSocietyEmail($this->getUser(), 'welcome');
                }
            }
        } else {
            $twigs['completed'] = false;
        }

        // return the response
        return $this->render('site/account/paid.twig', $twigs);
    }

    /**
     * Route for changing the account password.
     *
     * @param Request Symfony's request object.
     * @param UserHandler The user handler.
     * @param UserPasswordEncoderInterface Symfony's password encoder.
     * @return Response
     * @Route("/password", name="password")
     * @IsGranted("ROLE_USER")
     */
    public function password(
        Request $request,
        UserHandler $users,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'password', 'section' => 'account', 'title' => 'Change Password']
        ];

        // create and handle the change password form
        $changePasswordForm = $this->createForm(UserTypeChangePassword::class);
        $changePasswordForm->handleRequest($request);
        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $plainPassword = $changePasswordForm->get('new_password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($this->getUser(), $plainPassword);
            $this->getUser()->setPassword($encodedPassword);
            $users->saveUser($this->getUser());
            $this->addFlash('success', 'Your password has been changed.');
        }

        // add additional twig variables
        $twigs['changePasswordForm'] = $changePasswordForm->createView();

        // render and return the page
        return $this->render('site/account/password.twig', $twigs);
    }
}
