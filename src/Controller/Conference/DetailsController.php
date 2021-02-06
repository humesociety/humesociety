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
 * Controller for managing the current conference.
 *
 * @Route("/conference-manager/details", name="conference_details_")
 * @IsGranted("ROLE_ORGANISER")
 */
class DetailsController extends AbstractController
{
    /**
     * Route for viewing and editing details of the current conference.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param string $tab The initially visible tab.
     * @return Response
     * @Route("/{tab}", name="index", requirements={"tab": "details|files|deadline"})
     */
    public function index(Request $request, ConferenceHandler $conferences, string $tab = 'details'): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'manager',
            'subarea' => 'details',
            'tab' => $tab
        ];

        // look for the current conference
        $conference = $conferences->getCurrentConference();

        // return a basic page if there isn't one
        if (!$conference) {
            return $this->render('conference/no-current-conference.twig', $twigs);
        }

        // create and handle the conference details form
        $oldPath = $conference->getPath();
        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $conferenceForm->handleRequest($request);
        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferences->saveConference($conference, $oldPath);
            $this->addFlash('notice', 'Details for the '.$conference.' have been updated.');
        }

        // create and handle the new conference file upload form
        $upload = $conferences->createConferenceFile($conference);
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted()) {
            $twigs['tab'] = 'files';
            if ($uploadForm->isValid()) {
                $conferences->saveConferenceFile($upload, $conference);
                $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
            }
        }

        // add additional twig variables
        $twigs['conference'] = $conference;
        $twigs['conferenceForm'] = $conferenceForm->createView();
        $twigs['uploadForm'] = $uploadForm->createView();

        // render and return the page
        return $this->render('conference/details/edit.twig', $twigs);
    }

    /**
     * Route for deleting a conference file.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param string $filename The file's name.
     * @return Response
     * @Route("/delete/{filename}", name="delete_upload")
     */
    public function deleteUpload(Request $request, ConferenceHandler $conferences, string $filename): Response
    {
        // look for the current conference (and assume we find one, otherwise we wouldn't be here)
        $conference = $conferences->getCurrentConference();

        // initialise the twig variables
        $twigs = [
            'area' => 'manager',
            'subarea' => 'details',
            'conference' => $conference,
            'filename' => $filename
        ];

        // create and handle the delete file form
        $uploadForm = $this->createFormBuilder()->getForm();
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $conferences->deleteConferenceFile($filename, $conference);
            $this->addFlash('notice', 'File "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('conference_details_index', ['tab' => 'files']);
        }

        // add additional twig variables
        $twigs['uploadForm'] = $uploadForm->createView();

        // render and return the page
        return $this->render('conference/details/delete-upload.twig', $twigs);
    }
}
