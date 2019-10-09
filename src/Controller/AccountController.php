<?php

namespace App\Controller;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\DuesPayment\DuesPayment;
use App\Entity\DuesPayment\DuesPaymentHandler;
use App\Entity\Email\EmailHandler;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionHandler;
use App\Entity\Submission\SubmissionType;
use App\Entity\Text\TextHandler;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use App\Entity\User\UserTypeDetails;
use App\Entity\User\UserTypeChangePassword;
use App\Entity\User\UserTypeFullAvailability;
use App\Entity\User\UserTypePartialAvailability;
use App\Entity\User\UserTypeSettings;
use App\Entity\User\UserTypeVolunteer;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
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
            'page' => ['id' => 'details', 'section' => 'account'],
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
            'page' => ['id' => 'password', 'section' => 'account']
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

    /**
     * Route for submitting/viewing papers and reviews.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param EmailHandler The email handler.
     * @param SubmissionHandler The submission handler.
     * @param TextHandler The text handler.
     * @param UserHandler The user handler.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/research/{tab}", name="research", requirements={"tab": "availability|submissions|reviews"})
     * @IsGranted("ROLE_USER")
     */
    public function research(
        Request $request,
        ConferenceHandler $conferences,
        EmailHandler $emails,
        SubmissionHandler $submissions,
        TextHandler $texts,
        UserHandler $users,
        string $tab = 'availability'
    ): Response {
        // initialise the twig variables
        $twigs = [
            'page' => ['id' => 'research', 'section' => 'account'],
            'tab' => $tab
        ];

        // look for the current conference (possibly null)
        $conference = $conferences->getCurrentConference();

        // now do things a little differently depending on whether or not there's a current conference
        if ($conference) {
            // create the FULL research availability form
            $availabilityForm = $this->createForm(UserTypeFullAvailability::class, $this->getUser());
            $availabilityForm->handleRequest($request);

            // create and handle the submission form
            $submission = new Submission();
            $submission->setUser($this->getUser())->setConference($conference);
            $submissionForm = $this->createForm(SubmissionType::class, $submission);
            $submissionForm->handleRequest($request);
            if ($submissionForm->isSubmitted() && $conference->isOpen()) {
                $tab = 'submissions';
                if ($submissionForm->isValid()) {
                    $submissions->saveSubmission($submission);
                    $users->refreshUser($this->getUser());
                    $emails->sendSubmissionEmail($submission, 'submission');
                    $emails->sendSubmissionNotification($submission);
                    $message = 'Your paper has been submitted. A confirmation email has been '
                             . 'sent to '.$this->getUser()->getEmail();
                    $this->addFlash('success', $message);
                }
            } elseif ($submissionForm->isSubmitted() && $conference->isClosed()) {
                $submissionForm->get('title')->addError(new FormError('The submissions deadline has now passed.'));
            }

            // add additional twig variables
            $twigs['conference'] = $conference;
            $twigs['submissionForm'] = $submissionForm->createView();
            $twigs['guidanceText'] = $texts->getTextContentByLabel('submission');
        } else {
            // create the PARTIAL research availability form
            $availabilityForm = $this->createForm(UserTypeFullAvailability::class, $this->getUser());
            $availabilityForm->handleRequest($request);
        }

        // handle the research availability form
        $availabilityForm->handleRequest($request);
        if ($availabilityForm->isSubmitted() && $availabilityForm->isValid()) {
            $users->saveUser($this->getUser());
            $this->addFlash('success', 'Your availability has been updated.');
        }

        // add additional twig variables
        $twigs['availabilityForm'] = $availabilityForm->createView();

        // return the response
        return $this->render('site/account/research.twig', $twigs);
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
            'page' => ['id' => 'pay', 'section' => 'account'],
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
        // create the PayPal environment
        if ($_ENV['APP_ENV'] === 'prod') {
            $environment = new ProductionEnvironment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SECRET']);
        } else {
            $environment = new SandboxEnvironment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SECRET']);
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
                $duesPayments->saveDuesPayment($duesPayment);
                $users->updateDues($this->getUser(), $duesPayment);
                if ($newMember) {
                    $emails->sendSocietyEmail($this->getUser(), 'welcome');
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
