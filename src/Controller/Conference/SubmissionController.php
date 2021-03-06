<?php

namespace App\Controller\Conference;

use App\Entity\Chair\Chair;
use App\Entity\Chair\ChairHandler;
use App\Entity\Comment\Comment;
use App\Entity\Comment\CommentHandler;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\ConferenceEmailHandler;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewHandler;
use App\Entity\Invitation\InvitationTypeExisting;
use App\Entity\Invitation\InvitationTypeNew;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionTypeDecision;
use App\Entity\Submission\SubmissionHandler;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference submissions.
 *
 * @Route("/conference-manager/submission", name="conference_submission_")
 * @IsGranted("ROLE_ORGANISER")
 */
class SubmissionController extends AbstractController
{
    /**
     * Route for viewing all submissions to the current conference.
     *
     * @param ConferenceHandler $conferences The conference handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(ConferenceHandler $conferences): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'paper',
            'subarea' => 'submission'
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('conference/no-current-conference.twig', $twigs);
        }

        // add the conference and its submission keywords to the twig variables
        $twigs['conference'] = $conference;
        $twigs['keywords'] = $conferences->getSubmissionKeywords($conference);

        // render and return the page
        return $this->render('conference/submission/index.twig', $twigs);
    }

    /**
     * Route for updating the keywords for a submission.
     *
     * @param SubmissionHandler $submissions The submission handler.
     * @param Submission $submission The submission to edit.
     * @param string $keywords The new keywords.
     * @return JsonResponse
     * @Route("/keywords/{submission}/{keywords}", name="keywords")
     */
    public function updateKeywords(
        SubmissionHandler $submissions,
        Submission $submission,
        string $keywords
    ): JsonResponse {
        $submission->setKeywords($keywords);
        $submissions->saveSubmission($submission);
        return $this->json(['ok' => true]);
    }

    /**
     * Route for viewing submission details.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param SubmissionHandler $submissions The submission handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/view/{submission}", name="view")
     */
    public function view(
      Request $request,
      ConferenceHandler $conferences,
      SubmissionHandler $submissions,
      Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'paper',
            'subarea' => 'submission',
            'submission' => $submission
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // throw a 404 error if there isn't one or if it isn't the conference of the given submission
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // create and handle the submission decision form
        $submissionDecisionForm = $this->createForm(SubmissionTypeDecision::class, $submission);
        $submissionDecisionForm->handleRequest($request);
        if ($submissionDecisionForm->isSubmitted() && $submissionDecisionForm->isValid()) {
            $submissions->saveSubmission($submission);
            $this->addFlash('notice', 'The decision for this paper has been recorded.');
        }

        // add additional twig variables
        $twigs['submissionDecisionForm'] = $submissionDecisionForm->createView();

        // render and return the page
        return $this->render('conference/submission/view.twig', $twigs);
    }

    /**
     * Route for sending a decision email to a user who submitted a paper.
     *
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/email/decision/{submission}", name="email_decision")
     */
    public function emailDecision(
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        Submission $submission
    ): Response {
        // throw 404 error if the submission is not for the current conference
        if ($submission->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // check the email can be sent
        if ($submission->getDecisionEmailed() || $submission->getStatus() === 'pending') {
            throw $this->createNotFoundException('Page not found.');
        }

        // try to send the email
        try {
            switch ($submission->getStatus()) {
                case 'accepted':
                    $conferenceEmails->sendSubmissionEmail($submission, 'submission-acceptance');
                    break;
    
                case 'rejected':
                    $conferenceEmails->sendSubmissionEmail($submission, 'submission-rejection');
                    break;
            }
            $this->addFlash('notice', "{$submission->getUser()} has been sent an email informing them of the decision.");
        } catch (\Error $error) {
            $this->addFlash('error', $error->getMessage());
        }

        // return a redirect to the submission page
        return $this->redirectToRoute('conference_submission_view', [
            'submission' => $submission->getId()
        ]);
    }

    /**
     * Route for sending a submission remdinder email to a user who's paper has been accepted.
     *
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/email/reminder/{submission}", name="email_reminder")
     */
    public function emailReminder(
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        Submission $submission
    ): Response {
        // throw 404 error if the submission is not for the current conference
        if ($submission->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // check the email can be sent
        if (!$submission->isAccepted() || !$submission->getDecisionEmailed()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // try to send the reminder email
        try {
            $conferenceEmails->sendSubmissionEmail($submission, 'submission-reminder');
            $this->addFlash('notice', "{$submission->getUser()} has been sent an email reminding them to submit their paper.");
        } catch (\Error $error) {
            $this->addFlash('error', $error->getMessage());
        }

        // return a redirect to the submission page
        return $this->redirectToRoute('conference_submission_view', [
            'submission' => $submission->getId()
        ]);
    }
}
