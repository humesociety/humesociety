<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
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
     * The submissions index page.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_submission_view');
    }

    /**
     * The page for viewing all submissions.
     *
     * @param ConferenceHandler The conference handler.
     * @return Response
     * @Route("/view", name="view")
     */
    public function view(ConferenceHandler $conferenceHandler): Response
    {
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

        // return the response
        return $this->render('admin/conference/submission/view.twig', [
            'area' => 'conference',
            'subarea' => 'submission',
            'conference' => $conference,
            'keywords' => $conferenceHandler->getSubmissionKeywords($conference)
        ]);
    }

    /**
     * The page for an individual submission.
     *
     * @param ConferenceHandler The conference handler.
     * @param SubmissionHandler The submission handler.
     * @param Submission The submission.
     * @return Response
     * @Route("/details/{submission}", name="details")
     */
    public function details(
        ConferenceHandler $conferenceHandler,
        SubmissionHandler $submissionHandler,
        Submission $submission
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

        // return the response
        return $this->render('admin/conference/submission/details.twig', [
            'area' => 'conference',
            'subarea' => 'submission',
            'conference' => $conference,
            'submission' => $submission
        ]);
    }

    /**
     * Update the keywords for a submission.
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
