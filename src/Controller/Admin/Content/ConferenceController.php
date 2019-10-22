<?php

namespace App\Controller\Admin\Content;

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
 * Controller for editing conference data.
 *
 * @Route("/admin/content/conference", name="admin_content_conference_")
 * @IsGranted("ROLE_EVPT")
 */
class ConferenceController extends AbstractController
{
    /**
     * @Route("/{decade}", name="index", requirements={"decade": "\d{4}"})
     */
    public function index(ConferenceHandler $conferences, $decade = null): Response
    {
        return $this->render('admin/content/conference/view.twig', [
            'area' => 'content',
            'subarea' => 'conference',
            'decade' => $decade,
            'decades' => $conferences->getDecades(),
            'conferences' => $conferences->getConferences()
        ]);
    }

    /**
     * @Route("/edit/{id}/{tab}", name="edit")
     */
    public function edit(
        Conference $conference,
        ConferenceHandler $conferences,
        UploadHandler $uploadHandler,
        Request $request,
        string $tab = 'details'
    ): Response {
        // the conference form
        $conference = $conferences->enrichConference($conference);
        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $conferenceForm->handleRequest($request);

        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferenceHandler->saveConference($conference);
            $this->addFlash('notice', 'Details for the '.$conference.' have been updated.');
            return $this->redirectToRoute('admin_content_conference_index', [
                'decade' => $conference->getDecade()
            ]);
        }

        // the upload file
        $upload = new Upload();
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted()) {
            $tab = 'files';
            if ($uploadForm->isValid()) {
                $uploadHandler->saveConferenceFile($upload, $conference);
                $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
                return $this->redirectToRoute('admin_content_conference_edit', [
                    'id' => $conference->getId(),
                    'tab' => 'files'
                ]);
            }
        }

        // render the page
        return $this->render('admin/content/conference/edit.twig', [
            'area' => 'content',
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
    public function delete(
        Conference $conference,
        ConferenceHandler $conferenceHandler,
        Request $request
    ): Response {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $conferenceHandler->deleteConference($conference);
            $this->addFlash('notice', 'The record for the '.$conference.' has been deleted.');
            return $this->redirectToRoute('admin_content_conference_index');
        }

        return $this->render('admin/content/conference/delete.twig', [
            'area' => 'content',
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
            return $this->redirectToRoute('admin_content_conference_edit', [
                'id' => $conference->getId(),
                'tab' => 'files'
            ]);
        }

        return $this->render('admin/content/conference/delete-upload.twig', [
            'area' => 'content',
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
            $this->addFlash('notice', 'A record for the '.$conference.' has been created.');
            return $this->redirectToRoute('admin_content_conference_index', [
                'decade' => $conference->getDecade()
            ]);
        }

        return $this->render('admin/content/conference/create.twig', [
            'area' => 'content',
            'subarea' => 'conference',
            'conferenceForm' => $conferenceForm->createView()
        ]);
    }
}
