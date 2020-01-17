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
 * @Route("/conference-manager/review", name="conference_review_")
 * @IsGranted("ROLE_ORGANISER")
 */
class ReviewController extends AbstractController
{
    /**
     * Route for viewing reviews for all submissions to the current conference.
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
            'subarea' => 'review'
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
        return $this->render('conference/review/index.twig', $twigs);
    }

    /**
     * Route for handling reviews for a submission.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param ReviewHandler $reviews The review handler.
     * @param UserHandler $users The user handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/view/{submission}", name="view")
     */
    public function view(
        Request $request,
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        ReviewHandler $reviews,
        UserHandler $users,
        Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'paper',
            'subarea' => 'review',
            'submission' => $submission
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // throw a 404 error if there isn't one or if it isn't the conference of the given submission
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // create and handle the review invitation form for existing users
        $review1 = new Review($submission);
        $invitationExistingForm = $this->createForm(InvitationTypeExisting::class, $review1);
        $invitationExistingForm->handleRequest($request);
        if ($invitationExistingForm->isSubmitted() && $invitationExistingForm->isValid()) {
            $reviews->saveReview($review1);
            $conferenceEmails->sendReviewEmail($review1, 'review-invitation');
            $this->addFlash('notice', "A review invitation email has been sent to {$review1->getUser()}.");
        }

        // create and handle the review invitation form for new users
        $review2 = new Review($submission);
        $invitationNewForm = $this->createForm(InvitationTypeNew::class, $review2);
        $invitationNewForm->handleRequest($request);
        if ($invitationNewForm->isSubmitted() && $invitationNewForm->isValid()) {
            $user = $users->createInvitedUser($review2);
            $existing = $users->getUserByEmail($user->getEmail());
            if ($existing) {
                $error = new FormError('There is already a user with this email address in the database.');
                $invitationNewForm->get('email')->addError($error);
            } else {
                $users->saveUser($user);
                $review2->setUser($user);
                $reviews->saveReview($review2);
                $conferenceEmails->sendReviewEmail($review2, 'review-invitation');
                $this->addFlash('notice', "A review invitation email has been sent to {$review2->getUser()}.");
            }
        }

        // add additional twig variables
        $twigs['invitationExistingForm'] = $invitationExistingForm->createView();
        $twigs['invitationNewForm'] = $invitationNewForm->createView();

        // render and return the page
        return $this->render('conference/review/view.twig', $twigs);
    }

    /**
     * Route for deleting/revoking a review invitation.
     *
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param ReviewHandler $reviews The review handler.
     * @param Submission $submission The submission.
     * @param Review $review The review.
     * @return Response
     * @Route("/delete/{submission}/{review}", name="delete")
     */
     public function deleteReview(
         ConferenceEmailHandler $conferenceEmails,
         ReviewHandler $reviews,
         Submission $submission,
         Review $review
     ): Response {
         $conferenceEmails->sendReviewEmail($review, 'review-invitation-cancellation');
         $reviews->deleteReview($review);
         $this->addFlash('notice', "Review invitation to {$review->getUser()} has been revoked, and the cancellation email sent.");
         return $this->redirectToRoute('conference_review_view', [
             'submission' => $submission->getId()
         ]);
     }

     /**
      * Route for sending a reminder email for a review.
      *
      * @param ConferenceHandler $conferences The conference handler.
      * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
      * @param Review $review The review.
      * @return Response
      * @Route("/email/reminder/{review}", name="email_reminder")
      */
     public function emailReminder(
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
         return $this->redirectToRoute('conference_review_view', [
             'submission' => $review->getSubmission()->getId()
         ]);
     }
}
