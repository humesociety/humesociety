<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
use App\Entity\Reviewer\Reviewer;
use App\Entity\Reviewer\ReviewerType;
use App\Entity\Reviewer\ReviewerHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/conference/reviewer", name="admin_conference_reviewer_")
 * @IsGranted("ROLE_ORGANISER")
 *
 * Controller for managing conference reviewers.
 */
class ReviewerController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_reviewer_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(ReviewerHandler $reviewerHandler): Response
    {
        // return the result
        return $this->render('admin/conference/reviewer/view.twig', [
            'area' => 'conference',
            'subarea' => 'reviewer',
            'reviewers' => $reviewerHandler->getReviewers()
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(ReviewerHandler $reviewerHandler, Request $request): Response
    {
        // the reviewer form
        $reviewer = new Reviewer();
        $reviewerForm = $this->createForm(ReviewerType::class, $reviewer);
        $reviewerForm->handleRequest($request);
        if ($reviewerForm->isSubmitted() && $reviewerForm->isValid()) {
            $reviewerHandler->saveReviewer($reviewer);
            $this->addFlash('notice', 'A reviewer record has been created for '.$reviewer);
            return $this->redirectToRoute('admin_conference_reviewer_view');
        }

        // return the result
        return $this->render('admin/conference/reviewer/create.twig', [
            'area' => 'conference',
            'subarea' => 'reviewer',
            'reviewerForm' => $reviewerForm->createView()
        ]);
    }
}
