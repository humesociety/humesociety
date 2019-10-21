<?php

namespace App\Controller\Admin\User;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Chair\ChairHandler;
use App\Entity\Comment\CommentHandler;
use App\Entity\Paper\PaperHandler;
use App\Entity\Review\ReviewHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference invitations.
 *
 * @Route("/admin/user/invitation", name="admin_user_invitation_")
 * @IsGranted("ROLE_TECH")
 */
class InvitationController extends AbstractController
{
    /**
     * Route for viewing all invitations.
     *
     * @param ChairHandler The chair handler.
     * @param CommentHandler The comment handler.
     * @param ConferenceHandler The conference handler.
     * @param PaperHandler The paper handler.
     * @param ReviewHandler The review handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(
        ChairHandler $chairs,
        CommentHandler $comments,
        ConferenceHandler $conferences,
        PaperHandler $papers,
        ReviewHandler $reviews
    ): Response {
        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // initialise the twig variables
        $twigs = [
            'area' => 'user',
            'subarea' => 'invitation',
            'chairs' => $chairs->getChairs($conference),
            'comments' => $comments->getComments($conference),
            'papers' => $papers->getPapers($conference),
            'reviews' => $reviews->getReviews($conference)
        ];

        // render and return the page
        return $this->render('admin/user/invitation/index.twig', $twigs);
    }
}
