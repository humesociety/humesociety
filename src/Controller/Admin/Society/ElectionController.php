<?php

namespace App\Controller\Admin\Society;

use App\Entity\Election\Election;
use App\Entity\Election\ElectionHandler;
use App\Entity\Election\ElectionType;
use App\Entity\Candidate\CandidateHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for editing society elections.
 *
 * @Route("/admin/society/election", name="admin_society_election_")
 * @IsGranted("ROLE_EVPT")
 */
class ElectionController extends AbstractController
{
    /**
     * @Route("/{decade}", name="index", requirements={"deacde": "\d{4}"})
     */
    public function index(ElectionHandler $electionHandler, string $decade = null): Response
    {
        return $this->render('admin/society/election/view.twig', [
            'area' => 'society',
            'subarea' => 'election',
            'decade' => $decade,
            'decades' => $electionHandler->getDecades(),
            'elections' => $electionHandler->getElections()
        ]);
    }

    /**
     * @Route("/open/{id}", name="open")
     */
    public function open(Election $election, ElectionHandler $electionHandler): Response
    {
        $election->setOpen(true);
        $electionHandler->saveElection($election);
        $this->addFlash('notice', 'Election for '.$election.' has been opened. Editing of this election has been disabled.');
        return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
    }

    /**
     * @Route("/close/{id}", name="close")
     */
    public function close(Election $election, ElectionHandler $electionHandler): Response
    {
        $election->setOpen(false);
        $electionHandler->saveElection($election);
        $this->addFlash('notice', 'Election for '.$election.' has been closed. Editing of this election is now possible.');
        return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(
        Election $election,
        ElectionHandler $electionHandler,
        CandidateHandler $candidateHandler,
        Request $request
    ): Response {
        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $electionHandler->saveElection($election);
            $this->addFlash('notice', 'Election for '.$election.' has been updated.');
            return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
        }

        return $this->render('admin/society/election/edit.twig', [
            'area' => 'society',
            'subarea' => 'election',
            'election' => $election,
            'electionForm' => $form->createView(),
            'candidates' => $candidateHandler->getCandidatesByYear($election->getYear())
        ]);
    }

    /**
     * @Route("/candidates/{id}", name="candidates")
     */
    public function candidates(Election $election, CandidateHandler $candidateHandler): Response
    {
        return $this->render('admin/society/election/candidates.twig', [
            'area' => 'society',
            'subarea' => 'election',
            'election' => $election,
            'candidates' => $candidateHandler->getCandidatesByYear($election->getYear())
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Election $election, ElectionHandler $electionHandler, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $electionHandler->deleteElection($election);
            $this->addFlash('notice', 'Election for '.$election.' has been deleted.');
            return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
        }

        return $this->render('admin/society/election/delete.twig', [
            'area' => 'society',
            'subarea' => 'election',
            'election' => $election,
            'electionForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(ElectionHandler $electionHandler, Request $request): Response
    {
        $election = new Election();

        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $electionHandler->saveElection($election);
            $this->addFlash('notice', 'Election for '.$election.' has been created.');
            return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
        }

        return $this->render('admin/society/election/create.twig', [
            'area' => 'society',
            'subarea' => 'election',
            'electionForm' => $form->createView()
        ]);
    }
}
