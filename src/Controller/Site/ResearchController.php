<?php

namespace App\Controller\Site;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\ConferenceEmailHandler;
use App\Entity\Email\SystemEmailHandler;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionHandler;
use App\Entity\Submission\SubmissionType;
use App\Entity\Submission\SubmissionTypeFinal;
use App\Entity\Text\TextHandler;
use App\Entity\User\UserHandler;
use App\Entity\User\UserTypeFullAvailability;
use App\Entity\User\UserTypePartialAvailability;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the user account research area of the site.
 *
 * @Route("/account/research", name="account_research_")
 */
class ResearchController extends AbstractController
{
    /**
     * Route for updating review/comment/chair availability.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/availability", name="availability")
     * @IsGranted("ROLE_USER")
     * @throws \Exception
     */
    public function availability(Request $request, ConferenceHandler $conferences, UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'research', 'section' => 'account', 'title' => 'Research'],
            'tab' => 'availability'
        ];

        // look for the current conference (possibly null)
        $conference = $conferences->getCurrentConference();

        // create and handle the availability form
        $availabilityForm = $conference
            ? $this->createForm(UserTypeFullAvailability::class, $this->getUser())
            : $this->createForm(UserTypePartialAvailability::class, $this->getUser());
        $availabilityForm->handleRequest($request);
        if ($availabilityForm->isSubmitted() && $availabilityForm->isValid()) {
            $users->saveUser($this->getUser());
            $this->addFlash('success', 'Your availability has been updated.');
        }

        // add additional twig variables
        $twigs['conference'] = $conference;
        $twigs['availabilityForm'] = $availabilityForm->createView();

        // render and return the page
        return $this->render('site/account/research/availability.twig', $twigs);
    }

    /**
     * Route for submitting a paper to the Hume Conference.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param SystemEmailHandler $systemEmails The system email handler.
     * @param SubmissionHandler $submissions The submission handler.
     * @param TextHandler $texts The text handler.
     * @param string|null $reply
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @Route("/submissions/{reply}", name="submissions", requirements={"reply": "accept|decline"})
     * @IsGranted("ROLE_USER")
     */
    public function submissions(
        Request $request,
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        SystemEmailHandler $systemEmails,
        SubmissionHandler $submissions,
        TextHandler $texts,
        ?string $reply = null
    ): Response {
        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a 404 error if there isn't one
        if ($conference === null) {
            throw $this->createNotFoundException('Page not found.');
        }

        // get the current user's submission to the current conference (if any)
        $submission = $submissions->getSubmission($this->getUser(), $conference);

        // return a 404 error if there's no submission and the conference isn't open
        if ($submission === null && !$conference->isOpen()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'research', 'section' => 'account', 'title' => 'Research'],
            'tab' => 'submissions',
            'conference' => $conference,
            'submission' => $submission
        ];

        // if the current user hasn't submitted a paper yet...
        if ($submission === null && $conference->isOpen()) {
            // create and handle the submission form
            $submission = new Submission($this->getUser(), $conference);
            $submissionForm = $this->createForm(SubmissionType::class, $submission);
            $submissionForm->handleRequest($request);
            if ($submissionForm->isSubmitted() && $submissionForm->isValid()) {
                $submissions->saveSubmission($submission);
                $conferenceEmails->sendSubmissionEmail($submission, 'submission-acknowledgement');
                $systemEmails->sendSubmissionNotification($submission);
                $message = 'Your paper has been submitted. A confirmation email has been sent to '
                         . $this->getUser()->getEmail();
                $this->addFlash('success', $message);
            }
            $twigs['guidanceText'] = $texts->getTextContentByLabel('submission');
            $twigs['submissionForm'] = $submissionForm->createView();
        }

        // if there is a paper and it has been accepted...
        if ($submission && $submission->isAccepted()) {
            // maybe handle the reply...
            if ($reply) {
                $submission->setConfirmed($reply === 'accept');
                $submissions->saveSubmission($submission);
                // notify the conference organisers
                $systemEmails->sendSubmissionConfirmationNotification($submission);
            }

            // if the author has confirmed...
            // note: we include this form whether or not the final version has been submitted,
            // to allow users to upload new versions after the initial upload
            if ($submission->isConfirmed()) {
                // create and handle the submission final version upload form
                $finalSubmissionForm = $this->createForm(SubmissionTypeFinal::class, $submission);
                $finalSubmissionForm->handleRequest($request);
                if ($finalSubmissionForm->isSubmitted() && $finalSubmissionForm->isValid()) {
                    $submissions->saveSubmission($submission);
                    $systemEmails->sendSubmissionFinalNotification($submission);
                    $this->addFlash('success', 'The final version of your paper has been uploaded.');
                }
                $twigs['finalSubmissionForm'] = $finalSubmissionForm->createView();
            }
        }

        // render and return the page
        return $this->render('site/account/research/submissions.twig', $twigs);
    }

    /**
     * Route for checking how long till the current conference deadline.
     * @param ConferenceHandler $conferences
     * @return Response
     * @throws \Exception
     * @Route("/deadline", name="deadline")
     * @IsGranted("ROLE_USER")
     */
    public function deadline(ConferenceHandler $conferences): Response
    {
        $conference = $conferences->getCurrentConference();

        if ($conference && $conference->getActualDeadline()) {
            $countdown = $conference->getActualDeadline()->diff(new \DateTime('now'));
        } else {
            $countdown = null;
        }

        $twigs = [
            'conference' => $conference,
            'countdown' => $countdown
        ];

        return $this->render('site/account/research/deadline.twig', $twigs);
    }
}
