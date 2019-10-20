<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\ConferenceEmailHandler;
use App\Entity\Paper\Paper;
use App\Entity\Paper\PaperHandler;
use App\Entity\Invitation\InvitationTypeExisting;
use App\Entity\Invitation\InvitationTypeNew;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing invited speakers.
 *
 * @Route("/admin/conference/paper", name="admin_conference_paper_")
 * @IsGranted("ROLE_ORGANISER")
 */
class PaperController extends AbstractController
{
    /**
     * Route for viewing all invited papers and inviting new speakers.
     *
     * @param ConferenceHandler The conference handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(
        Request $request,
        ConferenceEmailHandler $conferenceEmails,
        ConferenceHandler $conferences,
        PaperHandler $papers,
        UserHandler $users
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'paper',
            'title' => 'Invited Papers'
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // throw a 404 error if there isn't one
        if (!$conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // create and handle the existing speaker invitation form
        $paper1 = new Paper($conference);
        $invitationExistingForm = $this->createForm(InvitationTypeExisting::class, $paper1);
        $invitationExistingForm->handleRequest($request);
        if ($invitationExistingForm->isSubmitted() && $invitationExistingForm->isValid()) {
            $papers->savePaper($paper1);
            $conferenceEmails->sendPaperEmail($paper1, 'paper-invitation');
            $this->addFlash('notice', "An invitation email has been sent to {$paper1->getUser()}.");
        }

        // create and handle the new speaker invitation form
        $paper2 = new Paper($conference);
        $invitationNewForm = $this->createForm(InvitationTypeNew::class, $paper2);
        $invitationNewForm->handleRequest($request);
        if ($invitationNewForm->isSubmitted() && $invitationNewForm->isValid()) {
            $user = $users->createInvitedUser($paper2);
            $existing = $users->getUserByEmail($user->getEmail());
            if ($existing) {
                $error = new FormError('There is already a user with this email address in the database.');
                $invitationNewForm->get('email')->addError($error);
            } else {
                $users->saveUser($user);
                $paper2->setUser($user);
                $papers->savePaper($paper2);
                $conferenceEmails->sendPaperEmail($paper2, 'paper-invitation');
                $this->addFlash('notice', "An invitation email has been sent to {$paper2->getUser()}.");
            }
        }

        // add additional twig variables
        $twigs['conference'] = $conference;
        $twigs['invitationExistingForm'] = $invitationExistingForm->createView();
        $twigs['invitationNewForm'] = $invitationNewForm->createView();

        // render and return the page
        return $this->render('admin/conference/paper/index.twig', $twigs);
    }

    /**
     * Route for sending a reminder email to an invited speaker.
     *
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param Paper The paper.
     * @return Response
     * @Route("/remind/{paper}", name="reminder")
     */
    public function remind(
        ConferenceHandler $conferences,
        ConferenceEmailHandler $conferenceEmails,
        Paper $paper
    ): Response {
        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // throw a 404 error if the paper isn't for the current conference
        if ($paper->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // send an email if the paper hasn't been submitted; otherwise throw 404 error
        switch ($paper->getStatus()) {
            case 'pending':
                $conferenceEmails->sendPaperEmail($paper, 'paper-invitation-reminder');
                break;

            default:
                throw $this->createNotFoundException('Page not found.');
        }

        // add flashbag notice, and then redirect to the details page for the relevant submission
        $this->addFlash('notice', "A reminder email has been sent to {$paper->getUser()}.");
        return $this->redirectToRoute('admin_conference_paper_index');
    }
}
