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
 * @Route("/admin/conference/conference", name="admin_conference_conference_")
 * @IsGranted("ROLE_ORGANISER")
 *
 * Controller for editing basic conference data.
 */
class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_conference_view');
    }

    /**
     * @Route("/view/{decade}", name="view")
     */
    public function view(ConferenceHandler $conferenceHandler, $decade = null): Response
    {
        return $this->render('admin/conference/conference/view.twig', [
            'area' => 'conference',
            'subarea' => 'conference',
            'decade' => $decade,
            'decades' => $conferenceHandler->getDecades(),
            'conferences' => $conferenceHandler->getConferences()
        ]);
    }

    /**
     * @Route("/edit/{id}/{tab}", name="edit")
     */
    public function edit(
        Conference $conference,
        ConferenceHandler $conferenceHandler,
        UploadHandler $uploadHandler,
        Request $request,
        string $tab = 'details'
    ): Response {
        $conference = $conferenceHandler->enrich($conference);
        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $conferenceForm->handleRequest($request);

        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferenceHandler->saveConference($conference);
            $this->addFlash('notice', 'Conference '.$conference.' has been updated.');
            return $this->redirectToRoute('admin_conference_conference_view', [
                'decade' => $conference->getDecade()
            ]);
        }

        $upload = new Upload();
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $uploadHandler->saveConferenceFile($upload, $conference);
            $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
            return $this->redirectToRoute('admin_conference_conference_edit', [
                'id' => $conference->getId(),
                'tab' => 'files'
            ]);
        }

        return $this->render('admin/conference/conference/edit.twig', [
            'area' => 'conference',
            'subarea' => 'conference',
            'tab' => $tab,
            'conference' => $conference,
            'conferenceForm' => $conferenceForm->createView(),
            'uploadForm' => $uploadForm->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Conference $conference, ConferenceHandler $conferenceHandler, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $conferenceHandler->deleteConference($conference);
            $this->addFlash('notice', 'Conference '.$conference.' has been deleted.');
            return $this->redirectToRoute('admin_conference_conference_view');
        }

        return $this->render('admin/conference/conference/delete.twig', [
            'area' => 'conference',
            'subarea' => 'conference',
            'conference' => $conference,
            'conferenceForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}/{filename}", name="delete_upload")
     */
    public function deleteUpload(
        Conference $conference,
        string $filename,
        ConferenceHandler $conferenceHandler,
        UploadHandler $uploadHandler,
        Request $request
    ): Response {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $uploadHandler->deleteConferenceFile($filename, $conference);
            $this->addFlash('notice', 'File "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_conference_conference_edit', [
                'id' => $conference->getId(),
                'tab' => 'files'
            ]);
        }

        return $this->render('admin/conference/conference/delete-upload.twig', [
            'area' => 'conference',
            'subarea' => 'conference',
            'conference' => $conference,
            'filename' => $filename,
            'uploadForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(ConferenceHandler $conferenceHandler, Request $request): Response
    {
        $conference = new Conference();
        $conference->setNumber($conferenceHandler->getNextNumber());
        $conference->setYear($conferenceHandler->getNextYear());

        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $conferenceForm->handleRequest($request);

        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferenceHandler->saveConference($conference);
            $this->addFlash('notice', 'Conference '.$conference.' has been created.');
            return $this->redirectToRoute('admin_conference_conference_view', [
                'decade' => $conference->getDecade()
            ]);
        }

        return $this->render('admin/conference/conference/create.twig', [
            'area' => 'conference',
            'subarea' => 'conference',
            'conferenceForm' => $conferenceForm->createView()
        ]);
    }
}
