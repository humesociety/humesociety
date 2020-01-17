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
 * Controller for managing comments/commentators for accepted conference submissions.
 *
 * @Route("/conference-manager/commentator", name="conference_commentator_")
 * @IsGranted("ROLE_ORGANISER")
 */
class CommentatorController extends AbstractController
{
    /**
     * Route for viewing comments/commentators for all accepted submissions to the current conference.
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
            'subarea' => 'commentator'
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
        return $this->render('conference/commentator/index.twig', $twigs);
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
     * @Route("/view/{submission}", name="view")
     */
    public function view(
        Request $request,
        CommentHandler $comments,
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        UserHandler $users,
        Submission $submission
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'paper',
            'subarea' => 'commentator',
            'submission' => $submission
        ];

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
        return $this->render('conference/commentator/view.twig', $twigs);
    }

    /**
     * Route for deleting/revoking a comment invitation.
     *
     * @param Request $request Symfony's request object.
     * @param CommentHandler $comments The comment handler.
     * @param Submission $submission The submission.
     * @param Comment $comment The comment.
     * @return Response
     * @Route("/delete/{submission}/{comment}", name="delete")
     */
    public function deleteComment(
        Request $request,
        CommentHandler $comments,
        Submission $submission,
        Comment $comment
    ): Response {
        $comments->deleteComment($comment);
        $this->addFlash('notice', "Comment invitation to {$comment->getUser()} has been deleted.");
        return $this->redirectToRoute('conference_commentator_view', [
            'submission' => $submission->getId()
        ]);
    }

    /**
     * Route for sending a reminder email for a comment.
     *
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param Comment $comment The comment.
     * @return Response
     * @Route("/email/reminder/{comment}", name="email_reminder")
     */
    public function emailReminder(
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
        return $this->redirectToRoute('conference_commentator_view', [
            'submission' => $comment->getSubmission()->getId()
        ]);
    }
}
