<?php

namespace App\Controller\Admin\Conference;

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
 * @Route("/admin/conference/submission", name="admin_conference_submission_")
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
        return $this->render('admin/conference/submission/index.twig', $twigs);
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
    * Create the initial twig variables for a submission-related page (used in subsequent routes).
    *
    * @param Submission $submission The submission.
    * @param string $tab The tab/page on display.
    * @return array
    */
    private function createSubmissionTwigs(Submission $submission, string $tab): array
    {
        return [
            'area' => 'conference',
            'subarea' => 'submission',
            'title' => (string) $submission,
            'tab' => $tab,
            'submission' => $submission
        ];
    }

    /**
     * Route for viewing submission details.
     *
     * @param ConferenceHandler $conferences The conference handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/details/{submission}", name="view")
     */
    public function view(
        ConferenceHandler $conferences,
        Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = $this->createSubmissionTwigs($submission, 'details');

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // throw a 404 error if there isn't one or if it isn't the conference of the given submission
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // render and return the page
        return $this->render('admin/conference/submission/view.twig', $twigs);
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
     * @Route("/details/{submission}/reviews", name="reviews")
     */
    public function reviews(
        Request $request,
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        ReviewHandler $reviews,
        UserHandler $users,
        Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = $this->createSubmissionTwigs($submission, 'reviews');

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
        return $this->render('admin/conference/submission/reviews.twig', $twigs);
    }

    /**
     * Route for deleting/revoking a review invitation.
     *
     * @param Request $request Symfony's request object.
     * @param ReviewHandler $reviews The review handler.
     * @param Submission $submission The submission.
     * @param Review $review The review.
     * @return Response
     * @Route("/details/{submission}/delete-review/{review}", name="delete_review")
     */
    public function deleteReview(
        Request $request,
        ReviewHandler $reviews,
        Submission $submission,
        Review $review
    ): Response {
        $reviews->deleteReview($review);
        $this->addFlash('notice', "Review invitation to {$review->getUser()} has been revoked.");
        return $this->redirectToRoute('admin_conference_submission_reviews', [
            'submission' => $submission->getId()
        ]);
    }

    /**
     * Route for recording the decision for a submission.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param SubmissionHandler $submissions The submission handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/details/{submission}/decision", name="decision")
     */
    public function decision(
        Request $request,
        ConferenceHandler $conferences,
        SubmissionHandler $submissions,
        Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = $this->createSubmissionTwigs($submission, 'decision');

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
        return $this->render('admin/conference/submission/decision.twig', $twigs);
    }

    /**
     * Route for handling comments for a submission.
     *
     * @param Request $request Symfony's request object.
     * @param CommentHandler $comments The comment handler.
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param UserHandler $users The user handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/details/{submission}/comments", name="comments")
     */
    public function comments(
        Request $request,
        CommentHandler $comments,
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        UserHandler $users,
        Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = $this->createSubmissionTwigs($submission, 'comments');

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // throw a 404 error if there isn't one or if it isn't the conference of the given submission
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // create and handle the comment invitation form for existing users
        $comment1 = new Comment($submission);
        $invitationExistingForm = $this->createForm(InvitationTypeExisting::class, $comment1);
        $invitationExistingForm->handleRequest($request);
        if ($invitationExistingForm->isSubmitted() && $invitationExistingForm->isValid()) {
            $comments->saveComment($comment1);
            $conferenceEmails->sendCommentEmail($comment1, 'comment-invitation');
            $this->addFlash('notice', "A comment invitation email has been sent to {$comment1->getUser()}.");
        }

        // create and handle the comment invitation form for new users
        $comment2 = new Comment($submission);
        $invitationNewForm = $this->createForm(InvitationTypeNew::class, $comment2);
        $invitationNewForm->handleRequest($request);
        if ($invitationNewForm->isSubmitted() && $invitationNewForm->isValid()) {
            $user = $users->createInvitedUser($comment2);
            $existing = $users->getUserByEmail($user->getEmail());
            if ($existing) {
                $error = new FormError('There is already a user with this email address in the database.');
                $invitationNewForm->get('email')->addError($error);
            } else {
                $users->saveUser($user);
                $comment2->setUser($user);
                $comments->saveComment($comment2);
                $conferenceEmails->sendCommentEmail($comment2, 'comment-invitation');
                $this->addFlash('notice', "A comment invitation email has been sent to {$comment2->getUser()}.");
            }
        }

        // add additional twig variables
        $twigs['invitationExistingForm'] = $invitationExistingForm->createView();
        $twigs['invitationNewForm'] = $invitationNewForm->createView();

        // render and return the page
        return $this->render('admin/conference/submission/comments.twig', $twigs);
    }

    /**
     * Route for handling chairs for a submission.
     *
     * @param Request $request Symfony's request object.
     * @param ChairHandler $chairs The chair handler.
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param UserHandler $users The user handler.
     * @param Submission $submission The submission.
     * @return Response
     * @Route("/details/{submission}/chair", name="chair")
     */
    public function chair(
        Request $request,
        ChairHandler $chairs,
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        UserHandler $users,
        Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = $this->createSubmissionTwigs($submission, 'chair');

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // throw a 404 error if there isn't one or if it isn't the conference of the given submission
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // create and handle the chair invitation form for existing users
        $chair1 = new Chair($submission);
        $invitationExistingForm = $this->createForm(InvitationTypeExisting::class, $chair1);
        $invitationExistingForm->handleRequest($request);
        if ($invitationExistingForm->isSubmitted() && $invitationExistingForm->isValid()) {
            $chairs->saveChair($chair1);
            $conferenceEmails->sendChairEmail($chair1, 'comment-invitation');
            $this->addFlash('notice', "A chair invitation email has been sent to {$chair1->getUser()}.");
        }

        // create and handle the chair invitation form for new users
        $chair2 = new Chair($submission);
        $invitationNewForm = $this->createForm(InvitationTypeNew::class, $chair2);
        $invitationNewForm->handleRequest($request);
        if ($invitationNewForm->isSubmitted() && $invitationNewForm->isValid()) {
            $user = $users->createInvitedUser($chair2);
            $existing = $users->getUserByEmail($user->getEmail());
            if ($existing) {
                $error = new FormError('There is already a user with this email address in the database.');
                $invitationNewForm->get('email')->addError($error);
            } else {
                $users->saveUser($user);
                $chair2->setUser($user);
                $chairs->saveChair($chair2);
                $conferenceEmails->sendChairEmail($chair2, 'chair-invitation');
                $this->addFlash('notice', "A chair invitation email has been sent to {$chair2->getUser()}.");
            }
        }

        // add additional twig variables
        $twigs['invitationExistingForm'] = $invitationExistingForm->createView();
        $twigs['invitationNewForm'] = $invitationNewForm->createView();

        // render and return the page
        return $this->render('admin/conference/submission/chair.twig', $twigs);
    }
}
