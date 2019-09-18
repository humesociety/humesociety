<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadHandler;
use App\Entity\Upload\UploadType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        return $this->render('admin/conference/submission/view.twig', [
            'area' => 'conference',
            'subarea' => 'submission',
            'conference' => $conferenceHandler->getCurrentConference()
        ]);
    }
}
