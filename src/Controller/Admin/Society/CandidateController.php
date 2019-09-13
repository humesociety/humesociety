<?php

namespace App\Controller\Admin\Society;

use App\Entity\Candidate\Candidate;
use App\Entity\Candidate\CandidateHandler;
use App\Entity\Candidate\CandidateType;
use App\Entity\Election\ElectionHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/society/candidate", name="admin_society_candidate_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for editing society committee members and candidates.
 */
class CandidateController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_society_candidate_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(CandidateHandler $candidateHandler): Response
    {
        return $this->render('admin/society/candidate/view.twig', [
            'area' => 'society',
            'subarea' => 'candidate',
            'years' => $candidateHandler->getYears(),
            'evpts' => $candidateHandler->getEVPTs(),
            'execs' => $candidateHandler->getExecs()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Candidate $candidate, CandidateHandler $candidateHandler, Request $request): Response
    {
        $candidateForm = $this->createForm(CandidateType::class, $candidate);
        $candidateForm->handleRequest($request);

        if ($candidateForm->isSubmitted() && $candidateForm->isValid()) {
            $candidateHandler->saveCandidate($candidate);
            $this->addFlash('notice', 'Record for '.$candidate.' has been updated.');
            return $this->redirectToRoute('admin_society_candidate_view');
        }

        return $this->render('admin/society/candidate/edit.twig', [
            'area' => 'society',
            'subarea' => 'candidate',
            'candidate' => $candidate,
            'candidateForm' => $candidateForm->createView()
        ]);
    }

    /**
     * @Route("/elect/{id}", name="elect")
     */
    public function elect(
        Candidate $candidate,
        CandidateHandler $candidateHandler,
        ElectionHandler $electionHandler
    ): Response {
        $candidate->setElected(true);
        $candidateHandler->saveCandidate($candidate);
        $this->addFlash('notice', $candidate.' has been elected to the executive committee.');
        $election = $electionHandler->getElectionByYear($candidate->getStart());
        return $this->redirectToRoute('admin_society_election_candidates', ['id' => $election->getId()]);
    }

    /**
     * @Route("/unelect/{id}", name="unelect")
     */
    public function unelect(
        Candidate $candidate,
        CandidateHandler $candidateHandler,
        ElectionHandler $electionHandler
    ): Response {
        $candidate->setElected(false);
        $candidateHandler->saveCandidate($candidate);
        $this->addFlash('notice', $candidate.' has been unelected from the executive committee.');
        $election = $electionHandler->getElectionByYear($candidate->getStart());
        return $this->redirectToRoute('admin_society_election_candidates', ['id' => $election->getId()]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(
        Candidate $candidate,
        CandidateHandler $candidateHandler,
        Request $request
    ): Response {
        $candidateForm = $this->createFormBuilder()->getForm();
        $candidateForm->handleRequest($request);

        if ($candidateForm->isSubmitted() && $form->isValid()) {
            $candidateHandler->deleteCandidate($candidate);
            $this->addFlash('notice', 'Record for '.$candidate.' has been deleted.');
            return $this->redirectToRoute('admin_society_candidate_view');
        }

        return $this->render('admin/society/candidate/delete.twig', [
            'area' => 'society',
            'subarea' => 'candidate',
            'candidate' => $candidate,
            'candidateForm' => $candidateForm->createView()
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(CandidateHandler $candidateHandler, Request $request): Response
    {
        $candidate = new Candidate();

        $candidateForm = $this->createForm(CandidateType::class, $candidate);
        $candidateForm->handleRequest($request);

        if ($candidateForm->isSubmitted() && $candidateForm->isValid()) {
            $candidateHandler->saveCandidate($candidate);
            $this->addFlash('notice', 'Record for '.$candidate.' has been created.');
            return $this->redirectToRoute('admin_society_candidate_view');
        }

        return $this->render('admin/society/candidate/create.twig', [
            'area' => 'society',
            'subarea' => 'candidate',
            'candidateForm' => $candidateForm->createView()
        ]);
    }
}
