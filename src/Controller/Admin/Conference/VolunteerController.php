<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Reviewer\Reviewer;
use App\Entity\Reviewer\ReviewerHandler;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for viewing volunteers.
 *
 * @Route("/admin/conference/volunteer", name="admin_conference_volunteer_")
 * @IsGranted("ROLE_ORGANISER")
 */
class VolunteerController extends AbstractController
{
    /**
     * The volunteers index page.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_volunteer_view');
    }

    /**
     * The page for viewing all volunteers.
     *
     * @param ConferenceHandler The conference handler.
     * @param UserHandler The user handler.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/view/{tab}", name="view", requirements={"tab": "review|comment|chair"})
     */
    public function view(
        ConferenceHandler $conferenceHandler,
        UserHandler $userHandler,
        $tab = 'review'
    ): Response {
        // look for the current conference
        $conference = $conferenceHandler->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', [
                'area' => 'conference',
                'subarea' => 'volunteers',
                'title' => 'Conference Volunteers'
            ]);
        }

        // return the response
        return $this->render('admin/conference/volunteer/view.twig', [
            'area' => 'conference',
            'subarea' => 'volunteer',
            'tab' => $tab,
            'conference' => $conference,
            'reviewers' => $userHandler->getReviewVolunteers(),
            'commentators' => $userHandler->getCommentVolunteers(),
            'chairs' => $userHandler->getChairVolunteers()
        ]);
    }

    /**
     * The page for creating a reviewer account for the given user.
     *
     * @param User The user.
     * @return Response
     * @Route("/create-reviewer/{user}", name="create_reviewer")
     */
    public function createReviewer(User $user, ReviewerHandler $reviewerHandler): Response
    {
        if ($user->getReviewer()) {
            $this->addFlash('notice', $user.' is already registered as a reviewer.');
        } else {
            $reviewer = new Reviewer();
            $reviewer->setUser($user);
            $reviewer->setPropertiesFromUser();
            $reviewerHandler->saveReviewer($reviewer);
            $this->addFlash('notice', 'A reviewer record has been created for '.$user);
        }
        return $this->redirectToRoute('admin_conference_volunteer_view');
    }
}
