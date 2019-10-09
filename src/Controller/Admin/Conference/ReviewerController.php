<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
use App\Entity\Reviewer\Reviewer;
use App\Entity\Reviewer\ReviewerHandler;
use App\Entity\Reviewer\ReviewerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference reviewers.
 *
 * @Route("/admin/conference/reviewer", name="admin_conference_reviewer_")
 * @IsGranted("ROLE_ORGANISER")
 */
class ReviewerController extends AbstractController
{
    /**
     * Route for viewing all reviewers.
     *
     * @param ReviewerHandler The reviewer handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(ReviewerHandler $reviewers): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'reviewer',
            'reviewers' => $reviewers->getReviewers()
        ];

        // render and return the page
        return $this->render('admin/conference/reviewer/view.twig', $twigs);
    }

    /**
     * Route for creating a new reviewer.
     *
     * @param Request Symfony's request object.
     * @param ReviewerHandler The reviewer handler.
     * @return Response
     * @Route("/create", name="create")
     */
    public function create(Request $request, ReviewerHandler $reviewers): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'reviewer',
        ];

        // create and handle the reviewer form
        $reviewer = new Reviewer();
        $reviewerForm = $this->createForm(ReviewerType::class, $reviewer);
        $reviewerForm->handleRequest($request);
        if ($reviewerForm->isSubmitted() && $reviewerForm->isValid()) {
            $reviewers->saveReviewer($reviewer);
            $this->addFlash('notice', 'A reviewer record has been created for '.$reviewer);
            return $this->redirectToRoute('admin_conference_reviewer_index');
        }

        // add additional twig variables
        $twigs['reviewerForm'] = $reviewerForm->createView();

        // render and return the page
        return $this->render('admin/conference/reviewer/create.twig', $twigs);
    }
}
