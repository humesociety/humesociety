<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Chair\Chair;
use App\Entity\Comment\Comment;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\ConferenceEmailHandler;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for sending submission-related emails.
 *
 * @Route("/admin/conference/submission/email", name="admin_conference_submission_email_")
 * @IsGranted("ROLE_ORGANISER")
 */
class SubmissionEmailController extends AbstractController
{
    /**
     * Route for sending a decision email to a user who submitted a paper.
     *
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param Submission The submission.
     * @return Response
     * @Route("/submission-decision/{submission}", name="submission_decision")
     */
    public function submissionDecision(
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

        switch ($submission->getStatus()) {
            case 'accepted':
                $conferenceEmails->sendSubmissionEmail($submission, 'submission-acceptance');
                break;

            case 'rejected':
                $conferenceEmails->sendSubmissionEmail($submission, 'submission-rejection');
                break;
        }

        // add flashbag notice
        $notice = "{$submission->getUser()} has been sent an email informing them of the decision.";
        $this->addFlash('notice', $notice);

        // return a redirect to the submission page
        return $this->redirectToRoute('admin_conference_submission_decision', [
            'submission' => $submission->getId()
        ]);
    }

    /**
     * Route for sending a submission remdinder email to a user who's paper has been accepted'.
     *
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param Submission The submission.
     * @return Response
     * @Route("/submission-reminder/{submission}", name="submission_reminder")
     */
    public function submissionReminder(
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        Submission $submission
    ): Response {
        // throw 404 error if the submission is not for the current conference
        if ($submission->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // check the email can be sent
        if ($submission->getStatus() !== 'accepted' || !$submission->getDecisionEmailed()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // send the reminder email
        $conferenceEmails->sendSubmissionEmail($submission, 'submission-reminder');

        // add flashbag notice
        $notice = "{$submission->getUser()} has been sent an email reminding them to submit their paper.";
        $this->addFlash('notice', $notice);

        // return a redirect to the submission page
        return $this->redirectToRoute('admin_conference_submission_index');
    }

    /**
     * Route for sending a reminder email for a review.
     *
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param Review The review.
     * @return Response
     * @Route("/review-reminder/{review}", name="review_reminder")
     */
    public function reviewReminder(
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        Review $review
    ): Response {
        // throw 404 error if the review is not for the current conference
        if ($review->getSubmission()->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // send an email if the review is pending or accepted; otherwise return 404 error
        switch ($review->getStatus()) {
            case 'pending':
                $conferenceEmails->sendReviewEmail($review, 'review-invitation-reminder');
                break;

            case 'accepted':
                $conferenceEmails->sendReviewEmail($review, 'review-submission-reminder');
                break;

            default:
                throw $this->createNotFoundException('Page not found.');
        }

        // add flashbag notice, and then redirect to the details page for the relevant submission
        $this->addFlash('notice', "A reminder email has been sent to {$review->getUser()}.");
        return $this->redirectToRoute('admin_conference_submission_reviews', [
            'submission' => $review->getSubmission()->getId()
        ]);
    }

    /**
     * Route for sending a reminder email for a comment.
     *
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param Comment The comment.
     * @return Response
     * @Route("/comment-reminder/{comment}", name="comment_reminder")
     */
    public function commentReminder(
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        Comment $comment
    ): Response {
        // throw 404 error if the review is not for the current conference
        if ($comment->getSubmission()->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // send an email if the review is pending or accepted; otherwise return 404 error
        switch ($comment->getStatus()) {
            case 'pending':
                $conferenceEmails->sendCommentEmail($comment, 'comment-invitation-reminder');
                break;

            case 'accepted':
                $conferenceEmails->sendCommentEmail($comment, 'comment-submission-reminder');
                break;

            default:
                throw $this->createNotFoundException('Page not found.');
        }

        // add flashbag notice, and then redirect to the details page for the relevant submission
        $this->addFlash('notice', "A reminder email has been sent to {$comment->getUser()}.");
        return $this->redirectToRoute('admin_conference_submission_comments', [
            'submission' => $comment->getSubmission()->getId()
        ]);
    }

    /**
     * Route for sending a reminder email for a chair invitation.
     *
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param Chair The chair invitation.
     * @return Response
     * @Route("/chair-reminder/{chair}", name="chair_reminder")
     */
    public function chairReminder(
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        Chair $chair
    ): Response {
        // throw 404 error if the review is not for the current conference
        if ($chair->getSubmission()->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // send an email if the invitation is pending; otherwise throw 404 error
        switch ($chair->getStatus()) {
            case 'pending':
                $conferenceEmails->sendChairEmail($chair, 'chair-invitation-reminder');
                break;

            default:
                throw $this->createNotFoundException('Page not found.');
        }

        // add flashbag notice, and then redirect to the details page for the relevant submission
        $this->addFlash('notice', "A reminder email has been sent to {$chair->getUser()}.");
        return $this->redirectToRoute('admin_conference_submission_chair', [
            'submission' => $chair->getSubmission()->getId()
        ]);
    }
}
