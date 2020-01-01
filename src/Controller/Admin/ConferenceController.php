<?php

namespace App\Controller\Admin;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Conference\ConferenceType;
use App\Entity\Upload\UploadType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for editing conference data.
 *
 * @Route("/admin/conference", name="admin_conference_")
 * @IsGranted("ROLE_EVPT")
 */
class ConferenceController extends AbstractController
{
    /**
     * Route for viewing conferences.
     *
     * @param ConferenceHandler $conferences The conference handler.
     * @param string|null $decade The decade of conferences to show.
     * @return Response
     * @Route("/{decade}", name="index", requirements={"decade": "\d{4}"})
     */
    public function index(ConferenceHandler $conferences, ?string $decade = null): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'conference',
            'decade' => $decade,
            'decades' => $conferences->getDecades(),
            'conferences' => $conferences->getConferences()
        ];

        // render and return the page
        return $this->render('admin/conference/view.twig', $twigs);
    }

    /**
     * Route for creating a new conference.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @return Response
     * @Route("/create", name="create")
     */
    public function create(Request $request, ConferenceHandler $conferences): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'conference'
        ];

        // create and handle the new conference form
        $conference = $conferences->createNextConference();
        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $conferenceForm->handleRequest($request);
        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferences->saveConference($conference);
            $this->addFlash('notice', 'A record for the '.$conference.' has been created.');
            return $this->redirectToRoute('admin_conference_index', [
                'decade' => $conference->getDecade()
            ]);
        }

        // add additional twig variables
        $twigs['conferenceForm'] = $conferenceForm->createView();

        // render and return the page
        return $this->render('admin/conference/create.twig', $twigs);
    }

    /**
     * Route for editing a conference.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param Conference $conference The conference to edit.
     * @param string $tab The initially visible tab.
     * @return Response
     * @Route("/edit/{id}/{tab}", name="edit")
     */
    public function edit(
        Request $request,
        ConferenceHandler $conferences,
        Conference $conference,
        string $tab = 'details'
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'conference',
            'tab' => $tab,
            'conference' => $conference
        ];

        // create and handle the conference form
        $conference = $conferences->enrichConference($conference);
        $conferenceForm = $this->createForm(ConferenceType::class, $conference);
        $conferenceForm->handleRequest($request);
        if ($conferenceForm->isSubmitted() && $conferenceForm->isValid()) {
            $conferences->saveConference($conference);
            $this->addFlash('notice', 'Details for the '.$conference.' have been updated.');
            return $this->redirectToRoute('admin_conference_index', [
                'decade' => $conference->getDecade()
            ]);
        }

        // create and handle the conference file form
        $upload = $conferences->createConferenceFile($conference);
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted()) {
            $twigs['tab'] = 'files';
            if ($uploadForm->isValid()) {
                $conferences->saveConferenceFile($upload, $conference);
                $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
                return $this->redirectToRoute('admin_conference_edit', [
                    'id' => $conference->getId(),
                    'tab' => 'files'
                ]);
            }
        }

        // add additional twig variables
        $twigs['conferenceForm'] = $conferenceForm->createView();
        $twigs['uploadForm'] = $uploadForm->createView();

        // render and return the page
        return $this->render('admin/conference/edit.twig', $twigs);
    }

    /**
     * Route for deleting a conference.
     *
     * @param Request $request Symfony's reguest object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param Conference $conference The conference to delete.
     * @return Response
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(
        Request $request,
        ConferenceHandler $conferences,
        Conference $conference
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'conference',
            'conference' => $conference
        ];

        // create and handle the delete conference form
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $conferences->deleteConference($conference);
            $this->addFlash('notice', 'The record for the '.$conference.' has been deleted.');
            return $this->redirectToRoute('admin_conference_index');
        }

        // add additional twig variables
        $twigs['conferenceForm'] = $form->createView();

        // render and return the page
        return $this->render('admin/conference/delete.twig', $twigs);
    }

    /**
     * Route for deleting a conference file.
     *
     * @param Request $request Symfony's request object.
     * @param ConferenceHandler $conferences The conference handler.
     * @param Conference $conference The conference.
     * @param string $filename The name of the file to delete.
     * @return Response
     * @Route("/delete/{id}/{filename}", name="delete_upload")
     */
    public function deleteUpload(
        Request $request,
        ConferenceHandler $conferences,
        Conference $conference,
        string $filename
    ): Response {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'conference',
            'conference' => $conference,
            'filename' => $filename
        ];

        // create and handle the delete file form
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $conferences->deleteConferenceFile($filename, $conference);
            $this->addFlash('notice', 'File "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_conference_edit', [
                'id' => $conference->getId(),
                'tab' => 'files'
            ]);
        }

        // add additional twig variables
        $twigs['uploadForm'] = $form->createView();

        // render and return the page
        return $this->render('admin/conference/delete-upload.twig', $twigs);
    }
}
