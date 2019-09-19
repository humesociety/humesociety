<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/conference/volunteer", name="admin_conference_volunteer_")
 * @IsGranted("ROLE_ORGANISER")
 *
 * Controller for viewing volunteers.
 */
class VolunteerController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_volunteer_view');
    }

    /**
     * @Route("/view/{tab}", name="view", requirements={"tab": "review|comment|chair"})
     */
    public function view(ConferenceHandler $conferenceHandler, UserHandler $userHandler, $tab = 'review'): Response
    {
        return $this->render('admin/conference/volunteer/view.twig', [
            'area' => 'conference',
            'subarea' => 'volunteer',
            'tab' => $tab,
            'conference' => $conferenceHandler->getCurrentConference(),
            'reviewers' => $userHandler->getReviewVolunteers(),
            'commentators' => $userHandler->getCommentVolunteers(),
            'chairs' => $userHandler->getChairVolunteers()
        ]);
    }
}
