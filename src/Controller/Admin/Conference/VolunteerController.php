<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceHandler;
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
     * Route for viewing all volunteers.
     *
     * @param ConferenceHandler The conference handler.
     * @param UserHandler The user handler.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/{tab}", name="index", requirements={"tab": "reviewers|commentators|chairs|speakers|active"})
     */
    public function index(ConferenceHandler $conferences, UserHandler $users, $tab = 'reviewers'): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'volunteer',
            'title' => 'Conference Volunteers',
            'tab' => $tab,
            'users' => $users->getUsers()
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // render and return the page now if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/volunteer/index.twig', $twigs);
        }

        // otherwise add additional twig variables
        $twigs['conference'] = $conference;
        $twigs['reviewers'] = $users->getReviewers($conference);
        $twigs['commentators'] = $users->getCommentators($conference);
        $twigs['chairs'] = $users->getChairs($conference);
        $twigs['speakers'] = $users->getSpeakers($conference);

        // render and return the page
        return $this->render('admin/conference/volunteer/index.twig', $twigs);
    }
}
