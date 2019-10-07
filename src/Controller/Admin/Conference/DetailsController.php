<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceType;
use App\Entity\Conference\ConferenceDeadlineType;
use App\Entity\Upload\UploadType;
use App\Service\ConferenceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing the current conference.
 *
 * @Route("/admin/conference/details", name="admin_conference_details_")
 * @IsGranted("ROLE_ORGANISER")
 */
class DetailsController extends AbstractController
{
    /**
     * Route for viewing and editing details of the current conference.
     *
     * @param Request Symfony's request object.
     * @param ConferenceManager The conference manager.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/{tab}", name="index", requirements={"tab": "details|files|deadline"})
     */
    public function index(Request $request, ConferenceManager $conferences, string $tab = 'details'): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'details',
            'title' => 'Conference Details',
            'tab' => $tab
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('admin/conference/no-current-conference.twig', $twigs);
        }

        // add the conference to the twig variables
        $twigs['conference'] = $conference;

        // create and handle the conference details form
        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $twigs['conferenceForm'] = $conferenceForm->createView();
        $conferenceForm->handleRequest($request);
        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferences->saveConference($conference);
            $this->addFlash('notice', 'Details for the '.$conference.' have been updated.');
        }

        // create and handle the new conference file upload form
        $upload = $conferences->createConferenceFile($conference);
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $twigs['uploadForm'] = $uploadForm->createView();
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted()) {
            $twigs['tab'] = 'files';
            if ($uploadForm->isValid()) {
                $conferences->saveConferenceFile($upload);
                $conferences->refresh($conference);
                $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
            }
        }

        // create and handle the conference deadline form
        $conferenceDeadlineForm = $this->createForm(ConferenceDeadlineType::class, $conference);
        $twigs['conferenceDeadlineForm'] = $conferenceDeadlineForm->createView();
        $conferenceDeadlineForm->handleRequest($request);
        if ($conferenceDeadlineForm->isSubmitted()) {
            $twigs['tab'] = 'deadline';
            if ($conferenceDeadlineForm->isValid()) {
                $conferences->saveConference($conference);
                $this->addFlash('notice', 'The deadline for the '.$conference.' has been updated.');
            }
        }

        // render and return the page
        return $this->render('admin/conference/details/edit.twig', $twigs);
    }

    /**
     * Route for deleting a conference file.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param string The file's name.
     * @return Response
     * @Route("/delete/{filename}", name="delete_upload")
     */
    public function deleteUpload(Request $request, ConferenceManager $conferences, string $filename): Response
    {
        // look for the current conference (and assume we find one, otherwise we wouldn't be here)
        $conference = $conferences->getCurrentConference();

        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'details',
            'conference' => $conference,
            'filename' => $filename
        ];

        // create and handle the delete file form
        $uploadForm = $this->createFormBuilder()->getForm();
        $twigs['uploadForm'] = $uploadForm->createView();
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $conferences->deleteConferenceFile($filename, $conference);
            $this->addFlash('notice', 'File "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_conference_details_index', ['tab' => 'files']);
        }

        // render and return the page
        return $this->render('admin/conference/details/delete-upload.twig', $twigs);
    }
}
