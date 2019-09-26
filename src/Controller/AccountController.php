<?php

namespace App\Controller;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\DuesPayment\DuesPayment;
use App\Entity\DuesPayment\DuesPaymentHandler;
use App\Entity\Email\EmailHandler;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionHandler;
use App\Entity\Submission\SubmissionType;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use App\Entity\User\UserDetailsType;
use App\Entity\User\UserChangePasswordType;
use App\Entity\User\UserFullAvailabilityType;
use App\Entity\User\UserPartialAvailabilityType;
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
 * The controller for the user account area of the site.
 *
 * @Route("/account", name="account_")
 */
class AccountController extends AbstractController
{
    /**
     * The index page; for editing basic account details.
     *
     * @param Request The Symfony HTTP request object.
     * @param UserHandler The user handler.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/{tab}", name="index", requirements={"tab": "details|settings"})
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, UserHandler $userHandler, $tab = 'details'): Response
    {
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

        // return the response
        return $this->render('site/account/index.twig', [
            'page' => ['id' => 'details', 'section' => 'account'],
            'tab' => $tab,
            'userDetailsForm' => $detailsForm->createView(),
            'userSettingsForm' => $settingsForm->createView()
        ]);
    }

    /**
     * The page for changing the account password.
     *
     * @param Request The Symfony HTTP request object.
     * @param UserHandler The user handler.
     * @param UserPasswordEncoderInterface The Symfony password encoder.
     * @return Response
     * @Route("/password", name="password")
     * @IsGranted("ROLE_USER")
     */
    public function password(
        Request $request,
        UserHandler $userHandler,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        // the change password form
        $form = $this->createForm(UserChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('new_password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($this->getUser(), $plainPassword);
            $this->getUser()->setPassword($encodedPassword);
            $userHandler->saveUser($this->getUser());
            $this->addFlash('success', 'Your password has been changed.');
        }

        // return the response
        return $this->render('site/account/password.twig', [
            'page' => ['id' => 'password', 'section' => 'account'],
            'userChangePasswordForm' => $form->createView()
        ]);
    }

    /**
     * The page for submitting/viewing papers and reviews.
     *
     * @param Request The Symfony HTTP request object.
     * @param ConferenceHandler The conference handler.
     * @param UserHandler The user handler.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/research/{tab}", name="research", requirements={"tab": "availability|submissions|reviews"})
     * @IsGranted("ROLE_USER")
     */
    public function research(
        Request $request,
        ConferenceHandler $conferenceHandler,
        EmailHandler $emailHandler,
        SubmissionHandler $submissionHandler,
        UserHandler $userHandler,
        $tab = 'availability'
    ): Response {
        // look for current conference
        $conference = $conferenceHandler->getCurrentConference();

        // research availability form
        $availabilityForm = $conference
            ? $this->createForm(UserFullAvailabilityType::class, $this->getUser())
            : $this->createForm(UserPartialAvailabilityType::class, $this->getUser());
        $availabilityForm->handleRequest($request);

        if ($availabilityForm->isSubmitted() && $availabilityForm->isValid()) {
            $userHandler->saveUser($this->getUser());
            $this->addFlash('success', 'Your availability has been updated.');
            return $this->redirectToRoute('account_research');
        }

        // conference submission form
        $submissionForm = null;
        $userCanSubmitToConference = false;
        if ($conference && $conference->isOpen()) {
            if (sizeof($this->getUser()->getSubmissions($conference)) == 0) {
                $userCanSubmitToConference = true;
                $submission = new Submission();
                $submission->setUser($this->getUser());
                $submission->setConference($conference);
                $submissionForm = $this->createForm(SubmissionType::class, $submission);
                $submissionForm->handleRequest($request);

                if ($submissionForm->isSubmitted()) {
                    $tab = 'submissions';
                    if ($submissionForm->isValid()) {
                        $submissionHandler->saveSubmission($submission);
                        $emailHandler->sendSubmissionAcknowledgementEmail($submission);
                        $this->addFlash('success', 'Your paper has been submitted. A confirmation email has been sent to '.$this->getUser()->getEmail());
                        return $this->redirectToRoute('account_research', ['tab' => 'submissions']);
                    }
                }
            }
        }

        // TODO: review forms ...

        // return the response
        return $this->render('site/account/research.twig', [
            'page' => ['id' => 'research', 'section' => 'account'],
            'tab' => $tab,
            'conference' => $conference,
            'userCanSubmitToConference' => $userCanSubmitToConference,
            'userAvailabilityForm' => $availabilityForm->createView(),
            'submissionForm' => $submissionForm ? $submissionForm->createView() : null,
            'submissions' => $this->getUser()->getSubmissions($conference)
        ]);
    }

    /**
     * The page for paying dues.
     *
     * @param Request The Symfony HTTP request object.
     * @return Response
     * @Route("/pay", name="pay")
     * @IsGranted("ROLE_USER")
     */
    public function pay(Request $request): Response
    {
        // check if the user can (and needs to) pay their dues
        if ($this->getUser()->getLifetimeMember() === true) {
            $payable = false;
        } elseif ($this->getUser()->isMember()) {
            $twoMonthsAway = new \DateTime('+2 months');
            $payable = $twoMonthsAway >= $this->getUser()->getDues();
        } else {
            $payable = true;
        }

        // return the response
        return $this->render('site/account/pay.twig', [
            'page' => ['id' => 'pay', 'section' => 'account'],
            'payable' => $payable,
            'dev' => ($_ENV['APP_ENV'] === 'dev')
        ]);
    }

    /**
     * The page PayPal send the user to after payment is completed.
     *
     * @param Request The Symfony HTTP request object.
     * @param DuesPaymentHandler The dues payment handler.
     * @param EmailHandler The email handler.
     * @param UserHandler The user handler.
     * @param string The PayPal order id.
     * @Route("/paid/{orderId}", name="paid")
     * @IsGranted("ROLE_USER")
     */
    public function paid(
        Request $request,
        DuesPaymentHandler $duesPaymentHandler,
        EmailHandler $emailHandler,
        UserHandler $userHandler,
        string $orderId
    ): Response {
        // create the PayPal environment
        if ($_ENV['APP_ENV'] === 'prod') {
            $environment = new ProductionEnvironment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SANDBOX_SECRET']);
        } else {
            $environment = new SandboxEnvironment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SANDBOX_SECRET']);
        }

        // create the PayPal client and check the order status
        $client = new PayPalHttpClient($environment);
        $response = $client->execute(new OrdersGetRequest($orderId));
        $result = $response->result;

        // if the payment is complete, update the database
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

        // return the response
        return $this->render('site/account/paid.twig', [
            'page' => ['id' => 'pay', 'section' => 'account'],
            'orderId' => $orderId,
            'result' => $result,
            'duesPayment' => $duesPayment
        ]);
    }
}
