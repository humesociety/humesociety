<?php

namespace App\Controller\Conference;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
use App\Entity\Conference\ConferenceTypeDeadline;
use App\Entity\Upload\UploadType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing the deadline for submissions to the current conference.
 *
 * @Route("/conference-manager/deadline", name="conference_deadline_")
 * @IsGranted("ROLE_ORGANISER")
 */
class DeadlineController extends AbstractController
{
    /**
     * Route for setting the deadline of the current conference.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(Request $request, ConferenceHandler $conferences): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'manager',
            'subarea' => 'deadline'
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('conference/no-current-conference.twig', $twigs);
        }

        // create and handle the conference deadline form
        $conferenceDeadlineForm = $this->createForm(ConferenceTypeDeadline::class, $conference);
        $conferenceDeadlineForm->handleRequest($request);
        if ($conferenceDeadlineForm->isSubmitted()) {
            $twigs['tab'] = 'deadline';
            if ($conferenceDeadlineForm->isValid()) {
                $conferences->saveConference($conference);
                $this->addFlash('notice', 'The deadline for the '.$conference.' has been updated.');
            }
        }

        // add additional twig variables
        $twigs['conference'] = $conference;
        $twigs['conferenceDeadlineForm'] = $conferenceDeadlineForm->createView();

        // render and return the page
        return $this->render('conference/deadline/edit.twig', $twigs);
    }
}
