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
 * Controller for editing society committee members and candidates.
 *
 * @Route("/admin/society/candidate", name="admin_society_candidate_")
 * @IsGranted("ROLE_EVPT")
 */
class CandidateController extends AbstractController
{
    /**
     * Route for viewing candidates.
     *
     * @param CandidateHandler $candidates The candidate handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(CandidateHandler $candidates): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'candidate',
            'years' => $candidates->getYears(),
            'evpts' => $candidates->getEvpts(),
            'execs' => $candidates->getExecs()
        ];

        // render and return the page
        return $this->render('admin/society/candidate/view.twig', $twigs);
    }

    /**
     * Route for creating a candidate.
     *
     * @param Request $request Symfony's request object.
     * @param CandidateHandler $candidates The candidate handler.
     * @return Response
     * @Route("/create", name="create")
     */
    public function create(Request $request, CandidateHandler $candidates): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'candidate'
        ];

        // create and handle the candidate form
        $candidate = new Candidate();
        $candidateForm = $this->createForm(CandidateType::class, $candidate);
        $candidateForm->handleRequest($request);
        if ($candidateForm->isSubmitted() && $candidateForm->isValid()) {
            $candidates->saveCandidate($candidate);
            $this->addFlash('notice', 'Record for '.$candidate.' has been created.');
            return $this->redirectToRoute('admin_society_candidate_index');
        }

        // add additional twig variables
        $twigs['candidateForm'] = $candidateForm->createView();

        // render and return the page
        return $this->render('admin/society/candidate/create.twig', $twigs);
    }

    /**
     * Route for editing a candidate.
     *
     * @param Request $request Symfony's request object.
     * @param CandidateHandler $candidates The candidate handler.
     * @param Candidate $candidate The candidate to edit.
     * @return Response
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Request $request, CandidateHandler $candidates, Candidate $candidate): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'candidate',
            'candidate' => $candidate
        ];

        // create and handle the candidate form
        $candidateForm = $this->createForm(CandidateType::class, $candidate);
        $candidateForm->handleRequest($request);
        if ($candidateForm->isSubmitted() && $candidateForm->isValid()) {
            $candidates->saveCandidate($candidate);
            $this->addFlash('notice', 'Record for '.$candidate.' has been updated.');
            return $this->redirectToRoute('admin_society_candidate_index');
        }

        // add additional twig variables
        $twigs['candidateForm'] = $candidateForm->createView();

        // render and return the page
        return $this->render('admin/society/candidate/edit.twig', $twigs);
    }

    /**
     * Route for electing a candidate.
     *
     * @param CandidateHandler $candidates The candidate handler.
     * @param ElectionHandler $elections The election handler.
     * @param Candidate $candidate The candidate to elect.
     * @return Response
     * @Route("/elect/{id}", name="elect")
     */
    public function elect(CandidateHandler $candidates, ElectionHandler $elections, Candidate $candidate): Response
    {
        $candidate->setElected(true);
        $candidates->saveCandidate($candidate);
        $this->addFlash('notice', $candidate.' has been elected to the executive committee.');
        $election = $elections->getElectionByYear($candidate->getStart());
        return $this->redirectToRoute('admin_society_election_candidates', ['id' => $election->getId()]);
    }

    /**
     * Route for unelecting a candidate.
     *
     * @param CandidateHandler $candidates The candidate handler.
     * @param ElectionHandler $elections The election handler.
     * @param Candidate $candidate The candidate to elect.
     * @return Response
     * @Route("/unelect/{id}", name="unelect")
     */
    public function unelect(CandidateHandler $candidates, ElectionHandler $elections, Candidate $candidate): Response
    {
        $candidate->setElected(false);
        $candidates->saveCandidate($candidate);
        $this->addFlash('notice', $candidate.' has been unelected from the executive committee.');
        $election = $elections->getElectionByYear($candidate->getStart());
        return $this->redirectToRoute('admin_society_election_candidates', ['id' => $election->getId()]);
    }

    /**
     * Route for deleting a candidate.
     *
     * @param Request $request Symfony's request object.
     * @param CandidateHandler $candidates The candidate handler.
     * @param Candidate $candidate The candidate to delete.
     * @return Response
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Request $request, CandidateHandler $candidates, Candidate $candidate): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'candidate',
            'candidate' => $candidate
        ];

        // create and handle the delete candidate form
        $candidateForm = $this->createFormBuilder()->getForm();
        $candidateForm->handleRequest($request);
        if ($candidateForm->isSubmitted() && $candidateForm->isValid()) {
            $candidates->deleteCandidate($candidate);
            $this->addFlash('notice', 'Record for '.$candidate.' has been deleted.');
            return $this->redirectToRoute('admin_society_candidate_index');
        }

        // add additional twig variables
        $twigs['candidateForm'] = $candidateForm->createView();

        // render and return the page
        return $this->render('admin/society/candidate/delete.twig', $twigs);
    }
}
