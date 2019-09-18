<?php

namespace App\Controller\Admin\Conference;

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
 * @Route("/admin/conference/details", name="admin_conference_details_")
 * @IsGranted("ROLE_ORGANISER")
 *
 * Controller for managing conference details.
 */
class DetailsController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_details_edit');
    }

    /**
     * @Route("/edit/{tab}", name="edit", requirements={"tab": "details|files|submissions"})
     */
    public function edit(
        ConferenceHandler $conferenceHandler,
        UploadHandler $uploadHandler,
        Request $request,
        string $tab = 'details'
    ): Response {
        $conference = $conferenceHandler->getCurrentConference();
        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $conferenceForm->handleRequest($request);

        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferenceHandler->saveConference($conference);
            $this->addFlash('notice', 'Conference details have been updated.');
            return $this->redirectToRoute('admin_conference_details_edit');
        }

        $upload = new Upload();
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $uploadHandler->saveConferenceFile($upload, $conference);
            $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
            return $this->redirectToRoute('admin_conference_details_edit', [
                'tab' => 'files'
            ]);
        }

        return $this->render('admin/conference/details/edit.twig', [
            'area' => 'conference',
            'subarea' => 'details',
            'tab' => $tab,
            'conference' => $conference,
            'conferenceForm' => $conferenceForm->createView(),
            'uploadForm' => $uploadForm->createView()
        ]);
    }

    /**
     * @Route("/delete/{filename}", name="delete_upload")
     */
    public function deleteUpload(
        string $filename,
        ConferenceHandler $conferenceHandler,
        UploadHandler $uploadHandler,
        Request $request
    ): Response {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $conference = $conferenceHandler->getCurrentConference();
            $uploadHandler->deleteConferenceFile($filename, $conference);
            $this->addFlash('notice', 'File "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_conference_details_edit', [
                'tab' => 'files'
            ]);
        }

        return $this->render('admin/conference/details/delete-upload.twig', [
            'area' => 'conference',
            'subarea' => 'details',
            'filename' => $filename,
            'uploadForm' => $form->createView()
        ]);
    }
}
