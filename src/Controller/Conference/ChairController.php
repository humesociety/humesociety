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
 * Controller for managing chairs for accepted conference submissions.
 *
 * @Route("/conference-manager/chair", name="conference_chair_")
 * @IsGranted("ROLE_ORGANISER")
 */
class ChairController extends AbstractController
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
            'subarea' => 'chair'
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
        return $this->render('conference/chair/index.twig', $twigs);
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
     * @Route("/view/{submission}", name="view")
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
        $twigs = [
            'area' => 'paper',
            'subarea' => 'chair',
            'submission' => $submission
        ];

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
        return $this->render('conference/chair/view.twig', $twigs);
    }

    /**
     * Route for deleting/revoking a chair invitation.
     *
     * @param Request $request Symfony's request object.
     * @param ChairHandler $chairs The chair handler.
     * @param Submission $submission The submission.
     * @param Chair $chair The chair invitation.
     * @return Response
     * @Route("/delete/{submission}/{chair}", name="delete")
     */
    public function deleteChair(
        Request $request,
        ChairHandler $chairs,
        Submission $submission,
        Chair $chair
    ): Response {
        $chairs->deleteChair($chair);
        $this->addFlash('notice', "Chair invitation to {$chair->getUser()} has been deleted.");
        return $this->redirectToRoute('conference_chair_view', [
            'submission' => $submission->getId()
        ]);
    }

    /**
     * Route for sending a reminder email for a chair invitation.
     *
     * @param ConferenceHandler $conferences The conference handler.
     * @param ConferenceEmailHandler $conferenceEmails The conference email handler.
     * @param Chair $chair The chair invitation.
     * @return Response
     * @Route("/email/reminder/{chair}", name="email_reminder")
     */
    public function emailReminder(
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
        return $this->redirectToRoute('conference_chair_view', [
            'submission' => $chair->getSubmission()->getId()
        ]);
    }
}
