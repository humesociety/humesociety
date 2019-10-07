<?php

namespace App\Controller\Admin\Conference;

use App\Service\ConferenceManager;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
use App\Entity\Email\EmailHandler;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewInvitationType;
use App\Entity\Review\ReviewHandler;
use App\Entity\Reviewer\ReviewerHandler;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionHandler;
use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadHandler;
use App\Entity\Upload\UploadType;
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
     * @Route(
     *     "/details/{submission}/{tab}",
     *     name="details",
     *     requirements={"tab": "submission|reviews|decision"}
     * )
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param EmailHandler The email handler.
     * @param ReviewHandler The review handler.
     * @param SubmissionHandler The submission handler.
     * @param string The initially visible tab.
     * @return Response
     */
    public function details(
        Request $request,
        ConferenceHandler $conferenceHandler,
        EmailHandler $emailHandler,
        ReviewHandler $reviewHandler,
        ReviewerHandler $reviewerHandler,
        SubmissionHandler $submissionHandler,
        Submission $submission,
        string $tab = 'submission'
    ): Response {
        // look for the current conference
        $conference = $conferenceHandler->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', [
                'area' => 'conference',
                'subarea' => 'submission',
                'title' => 'Conference Submissions'
            ]);
        }

        // only allow access to submissions to the current conference
        if ($submission->getConference() !== $conference) {
            throw $this->createNotFoundException('Page not found.');
        }

        // the review invitation form
        $review = new Review();
        $review->setSubmission($submission);
        $reviewInvitationForm = $this->createForm(ReviewInvitationType::class, $review);
        $reviewInvitationForm->handleRequest($request);
        if ($reviewInvitationForm->isSubmitted()) {
            $tab = 'reviews';
            if ($reviewInvitationForm->isValid()) {
                $reviewHandler->saveReview($review);
                $emailHandler->sendReviewEmail($review, 'review');
                $submissionHandler->refreshSubmission($submission);
                $this->addFlash('notice', 'A review invitation email has been sent to '.$review->getReviewer().'.');
            }
        }

        // return the response
        return $this->render('admin/conference/submission/details.twig', [
            'area' => 'conference',
            'subarea' => 'submission',
            'conference' => $conference,
            'submission' => $submission,
            'tab' => $tab,
            'reviewers' => $reviewerHandler->getReviewers(),
            'reviewInvitationForm' => $reviewInvitationForm->createView()
        ]);
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
    public function keywords(
        SubmissionHandler $submissionHandler,
        Submission $submission,
        string $keywords
    ): JsonResponse {
        $submission->setKeywords($keywords);
        $submissionHandler->saveSubmission($submission);
        return $this->json(['ok' => true]);
    }
}
