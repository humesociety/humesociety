<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Review\ReviewInvitationType;
use App\Entity\Submission\Submission;
use App\Service\ConferenceManager;
use App\Service\Emailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference submissions.
 *
 * @Route("/admin/conference/submission", name="admin_conference_submission_")
 * @IsGranted("ROLE_ORGANISER")
 */
class SubmissionController extends AbstractController
{
    /**
     * Route for viewing all submissions to the current conference.
     *
     * @param ConferenceManager The conference manager.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(ConferenceManager $conferences): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'submission',
            'title' => 'Conference Submissions'
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', $twigs);
        }

        // add the conference and its submission keywords to the twig variables
        $twigs['conference'] = $conference;
        $twigs['keywords'] = $conferences->getSubmissionKeywords($conference);

        // render and return the page
        return $this->render('admin/conference/submission/view.twig', $twigs);
    }

    /**
     * @Route("/details/{submission}/{tab}", name="details", requirements={"tab": "submission|reviews|decision"})
     *
     * @param Request Symfony's request object.
     * @param ConferenceManager The conference manager.
     * @param Emailer The emailer service.
     * @param Submission The submission.
     * @param string The initially visible tab.
     * @return Response
     */
    public function details(
        Request $request,
        ConferenceManager $conferences,
        Emailer $emailer,
        Submission $submission,
        string $tab = 'submission'
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'submission',
            'title' => 'Conference Submissions',
            'tab' => $tab
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', $twigs);
        }

        // only allow access to submissions to the current conference
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // add the current conference and reviewers to the twig variables
        $twigs['conference'] = $conference;
        $twigs['reviewers'] = $conferences->getReviewers();

        // create and handle the review invitation form
        $review = new Review();
        $review->setSubmission($submission);
        $reviewInvitationForm = $this->createForm(ReviewInvitationType::class, $review);
        $twigs['reviewInvitationForm'] = $reviewInvitationForm->createView();
        $reviewInvitationForm->handleRequest($request);
        if ($reviewInvitationForm->isSubmitted()) {
            $tab = 'reviews';
            if ($reviewInvitationForm->isValid()) {
                $conferences->saveReview($review);
                $emailer->sendReviewEmail($review, 'review');
                $this->addFlash('notice', 'A review invitation email has been sent to '.$review->getReviewer().'.');
            }
        }

        // render and return the page
        return $this->render('admin/conference/submission/details.twig', $twigs);
    }

    /**
     * Route for updating the keywords for a submission.
     *
     * @param SubmissionHandler The submission handler.
     * @param Submission The submission to edit.
     * @param string The new keywords.
     * @return JsonResponse
     * @Route("/keywords/{submission}/{keywords}", name="keywords")
     */
    public function updateKeywords(ConferenceManager $conferences, Submission $submission, string $keywords): JsonResponse
    {
        $submission->setKeywords($keywords);
        $conferences->saveSubmission($submission);
        return $this->json(['ok' => true]);
    }
}
