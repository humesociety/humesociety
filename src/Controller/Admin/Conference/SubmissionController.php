<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewInvitationType;
use App\Entity\Review\ReviewHandler;
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
 * @Route("/admin/conference/submission", name="admin_conference_submission_")
 * @IsGranted("ROLE_ORGANISER")
 *
 * Controller for managing conference submissions.
 */
class SubmissionController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_submission_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(ConferenceHandler $conferenceHandler): Response
    {
        // get the current conference
        $conference = $conferenceHandler->getCurrentConference();

        // return basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', [
                'area' => 'conference',
                'subarea' => 'submission',
                'title' => 'Conference Submissions'
            ]);
        }

        // return the result
        return $this->render('admin/conference/submission/view.twig', [
            'area' => 'conference',
            'subarea' => 'submission',
            'conference' => $conference,
            'keywords' => $conferenceHandler->getSubmissionKeywords($conference)
        ]);
    }

    /**
     * @Route(
     *     "/details/{submission}/{tab}",
     *     name="details",
     *     requirements={"tab": "submission|reviews|decision"}
     * )
     */
    public function details(
        ConferenceHandler $conferenceHandler,
        ReviewHandler $reviewHandler,
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

        // the review invitation form
        $review = new Review();
        $review->setSubmission($submission);
        $reviewInvitationForm = $this->createForm(ReviewInvitationType::class, $review);
        if ($reviewInvitationForm->isSubmitted()) {
            $tab = 'reviews';
            if ($reviewInvitationForm->isValid()) {
                $reviewHandler->saveReview($review);
                $emailHandler->sendConferenceEmail($review->getReviewer(), $submission, 'review');
                $submissionHandler->refreshSubmission($submission);
                $this->addFlash('notice', 'A review invitation email has been sent to '.$review->getReviewer().'.');
            }
        }

        // return the result
        return $this->render('admin/conference/submission/details.twig', [
            'area' => 'conference',
            'subarea' => 'submission',
            'conference' => $conference,
            'submission' => $submission,
            'tab' => $tab,
            'reviewInvitationForm' => $reviewInvitationForm->createView()
        ]);
    }

    /**
     * Update the keywords for a submission.
     *
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
