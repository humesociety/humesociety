<?php

namespace App\Controller\Admin\Society;

use App\Entity\Election\Election;
use App\Entity\Election\ElectionHandler;
use App\Entity\Election\ElectionType;
use App\Entity\Candidate\CandidateHandler;
use App\Entity\User\UserHandler;
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
     * Route for viewing elections.
     *
     * @param ElectionHandler $elections The election handler.
     * @param string $decade The initial decade to show.
     * @return Response
     * @Route("/{decade}", name="index", requirements={"decade": "\d{4}"})
     */
    public function index(ElectionHandler $elections, string $decade = null): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'election',
            'decade' => $decade,
            'decades' => $elections->getDecades(),
            'elections' => $elections->getElections()
        ];

        // render and return the page
        return $this->render('admin/society/election/view.twig', $twigs);
    }

    /**
     * Route for creating an election.
     *
     * @param Request $request Symfony's request object.
     * @param ElectionHandler $elections The election handler.
     * @return Response
     * @Route("/create", name="create")
     */
    public function create(Request $request, ElectionHandler $elections): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'election'
        ];

        // create and handle the election form
        $election = new Election();
        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $elections->saveElection($election);
            $this->addFlash('notice', 'Election for '.$election.' has been created.');
            return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
        }

        // add additional twig variables
        $twigs['electionForm'] = $form->createView();

        // render and return the page
        return $this->render('admin/society/election/create.twig', $twigs);
    }

    /**
     * Route for opening an election.
     *
     * @param ElectionHandler $elections The election handler.
     * @param Election $election The election to open.
     * @return Response
     * @Route("/open/{id}", name="open")
     */
    public function open(ElectionHandler $elections, Election $election): Response
    {
        $election->setOpen(true);
        $elections->saveElection($election);
        $this->addFlash('notice', 'Election for '.$election.' has been opened. Editing of this election has been disabled.');
        return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
    }

    /**
     * Route for closing an election.
     *
     * @param ElectionHandler $elections The election handler.
     * @param UserHandler $users
     * @param Election $election The election to open.
     * @return Response
     * @throws \Exception
     * @Route("/close/{id}", name="close")
     */
    public function close(ElectionHandler $elections, UserHandler $users, Election $election): Response
    {
        $election->setOpen(false);
        $election->setPopulation(sizeof($users->getMembersInGoodStanding()));
        $elections->saveElection($election);
        $users->resetVotingRecords();
        $this->addFlash('notice', 'Election for '.$election.' has been closed. Editing of this election is now possible.');
        return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
    }

    /**
     * Route for editing an election.
     *
     * @param Request $request Symfony's request object.
     * @param ElectionHandler $elections The election handler.
     * @param CandidateHandler $candidates The candidate handler.
     * @param Election $election The election to edit.
     * @return Response
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Request $request, ElectionHandler $elections, CandidateHandler $candidates, Election $election): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'election',
            'election' => $election,
            'candidates' => $candidates->getCandidatesByYear($election->getYear())
        ];

        // create and handle the election form
        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $elections->saveElection($election);
            $this->addFlash('notice', 'Election for '.$election.' has been updated.');
            return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
        }

        // add additional twig variables
        $twigs['electionForm'] = $form->createView();

        // render and return the page
        return $this->render('admin/society/election/edit.twig', $twigs);
    }

    /**
     * Route for viewing the candidate for an election.
     *
     * @param CandidateHandler $candidates The candidate handler.
     * @param Election $election The election.
     * @return Response
     * @Route("/candidates/{id}", name="candidates")
     */
    public function candidates(CandidateHandler $candidates, Election $election): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'election',
            'election' => $election,
            'candidates' => $candidates->getCandidatesByYear($election->getYear())
        ];

        // render and return the page
        return $this->render('admin/society/election/candidates.twig', $twigs);
    }

    /**
     * Route for deleting an election.
     *
     * @param Request $request Symfony's request object.
     * @param ElectionHandler $elections The election handler.
     * @param Election $election The election to delete.
     * @return Response
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Request $request, ElectionHandler $elections, Election $election): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'election',
            'election' => $election
        ];

        // create and handle the delete election form
        $electionForm = $this->createFormBuilder()->getForm();
        $electionForm->handleRequest($request);
        if ($electionForm->isSubmitted() && $electionForm->isValid()) {
            $elections->deleteElection($election);
            $this->addFlash('notice', 'Election for '.$election.' has been deleted.');
            return $this->redirectToRoute('admin_society_election_index', ['decade' => $election->getDecade()]);
        }

        // add additional twig variables
        $twigs['electionForm'] = $electionForm->createView();

        // render and return the page
        return $this->render('admin/society/election/delete.twig', $twigs);
    }
}
