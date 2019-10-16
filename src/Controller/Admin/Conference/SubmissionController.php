<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\EmailHandler;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewHandler;
use App\Entity\Review\ReviewTypeInvitation;
use App\Entity\Reviewer\ReviewerHandler;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionTypeDecision;
use App\Entity\Submission\SubmissionHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference submissions.
 *
 * @Route("/admin/conference/submission", name="admin_conference_submission_")
 * @IsGranted("ROLE_ORGANISER")
 */
class SubmissionController extends AbstractController
{
    /**
     * Route for viewing all submissions to the current conference.
     *
     * @param ConferenceHandler The conference handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(ConferenceHandler $conferences): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'submission',
            'title' => 'Conference Submissions'
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', $twigs);
        }

        // add the conference and its submission keywords to the twig variables
        $twigs['conference'] = $conference;
        $twigs['keywords'] = $conferences->getSubmissionKeywords($conference);

        // render and return the page
        return $this->render('admin/conference/submission/view.twig', $twigs);
    }

    /**
     * @Route("/details/{submission}/{tab}", name="details", requirements={"tab": "submission|reviews|decision"})
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param EmailHandler The email handler.
     * @param ReviewHandler The review handler.
     * @param ReviewerHandler The reviewer handler.
     * @param SubmissionHandler The submission handler.
     * @param Submission The submission.
     * @param string The initially visible tab.
     * @return Response
     */
    public function details(
        Request $request,
        ConferenceHandler $conferences,
        EmailHandler $emails,
        ReviewHandler $reviews,
        ReviewerHandler $reviewers,
        SubmissionHandler $submissions,
        Submission $submission,
        string $tab = 'submission'
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'submission',
            'title' => 'Conference Submissions',
            'tab' => $tab,
            'submission' => $submission
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', $twigs);
        }

        // only allow access to submissions to the current conference
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // add the current conference and reviewers to the twig variables
        $twigs['conference'] = $conference;
        $twigs['reviewers'] = $reviewers->getReviewers();

        // create and handle the review invitation form
        $review = new Review($submission);
        $reviewInvitationForm = $this->createForm(ReviewTypeInvitation::class, $review);
        $reviewInvitationForm->handleRequest($request);
        if ($reviewInvitationForm->isSubmitted()) {
            $twigs['tab'] = 'reviews';
            if ($reviewInvitationForm->isValid()) {
                $reviews->saveReview($review);
                $emails->sendReviewEmail($review, 'review');
                $this->addFlash('notice', 'A review invitation email has been sent to '.$review->getReviewer().'.');
            }
        }

        // create and handle the submission decision form
        $submissionDecisionForm = $this->createForm(SubmissionTypeDecision::class, $submission);
        $submissionDecisionForm->handleRequest($request);
        if ($submissionDecisionForm->isSubmitted()) {
            $twigs['tab'] = 'decision';
            if ($submissionDecisionForm->isValid()) {
                $submissions->saveSubmission($submission);
                $this->addFlash('notice', 'The decision for this paper has been recorded.');
            }
        }

        // add additional twig variables
        $twigs['reviewInvitationForm'] = $reviewInvitationForm->createView();
        $twigs['submissionDecisionForm'] = $submissionDecisionForm->createView();

        // render and return the page
        return $this->render('admin/conference/submission/details.twig', $twigs);
    }

    /**
     * Route for updating the keywords for a submission.
     *
     * @param SubmissionHandler The submission handler.
     * @param Submission The submission to edit.
     * @param string The new keywords.
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
     * Route for sending a reminder email for a review.
     *
     * @param EmailHandler The email handler.
     * @param Review The review.
     * @return Response
     * @Route("/remind/{review}", name="remind")
     */
    public function remind(EmailHandler $emails, Review $review): Response
    {
        // send an email if the review is pending or accepted; otherwise return 404 error
        switch ($review->getStatus()) {
            case 'pending':
                $emails->sendReviewEmail($review, 'pending-reminder');
                break;

            case 'accepted':
                $emails->sendReviewEmail($review, 'accepted-reminder');
                break;
            default:
                throw $this->createNotFoundException('Page not found.');
        }

        // add flashbag notice, and then redirect to the details page for the relevant submission
        $this->addFlash('notice', 'A reminder email has been sent to '.$review->getReviewer().'.');
        return $this->redirectToRoute('admin_conference_submission_details', [
            'submission' => $review->getSubmission()->getId(),
            'tab' => 'reviews'
        ]);
    }

    /**
     * Route for sending a decision email to a user who submitted a paper.
     *
     * @param EmailHandler The email handler.
     * @param Submission The submission.
     * @return Response
     * @Route("/email-decision/{submission}", name="email_decision")
     */
    public function emailDecision(
        EmailHandler $emails,
        SubmissionHandler $submissions,
        Submission $submission
    ): Response {
        // check the email can be sent
        if ($submission->getDecisionEmailed() || $submission->getStatus() === 'pending') {
            throw $this->createNotFoundException('Page not found.');
        }

        // send the email
        switch ($submission->getStatus()) {
            case 'accepted':
                $emails->sendSubmissionEmail($submission, 'accept');
                break;

            case 'rejected':
                $emails->sendSubmissionEmail($submission, 'reject');
                break;
        }

        // record that the email has been sent and add a flashbag message
        $submission->setDecisionEmailed(true);
        $submissions->saveSubmission($submission);
        $this->addFlash('notice', "{$submission->getUser()} has been sent an email informing them of the decision.");

        // return a redirect to the submission page
        return $this->redirectToRoute('admin_conference_submission_details', [
          'submission' => $submission->getId(),
          'tab' => 'decision'
        ]);
    }
}
