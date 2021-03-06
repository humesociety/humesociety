<?php

namespace App\Controller\Tech;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Chair\ChairHandler;
use App\Entity\Comment\CommentHandler;
use App\Entity\Paper\PaperHandler;
use App\Entity\Review\ReviewHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference invitations.
 *
 * @Route("/tech/invitation", name="tech_invitation_")
 * @IsGranted("ROLE_TECH")
 */
class InvitationController extends AbstractController
{
    /**
     * Route for viewing all invitations.
     *
     * @param ChairHandler $chairs The chair handler.
     * @param CommentHandler $comments The comment handler.
     * @param ConferenceHandler $conferences The conference handler.
     * @param PaperHandler $papers The paper handler.
     * @param ReviewHandler $reviews The review handler.
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
            'area' => 'invitation',
            'subarea' => 'invitation',
            'chairs' => $chairs->getChairs($conference),
            'comments' => $comments->getComments($conference),
            'papers' => $papers->getPapers($conference),
            'reviews' => $reviews->getReviews($conference)
        ];

        // render and return the page
        return $this->render('tech/invitation/index.twig', $twigs);
    }
}
