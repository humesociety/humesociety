<?php

namespace App\Controller\Admin\Journal;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueHandler;
use App\Entity\Issue\IssueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/journal/issue", name="admin_journal_issue_")
 * @IsGranted("ROLE_EDITOR")
 *
 * This is the controller for managing Hume Studies issues.
 */
class IssueController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_journal_issue_view');
    }

    /**
     * @Route("/view/{decade}", name="view")
     */
    public function view(IssueHandler $issueHandler, $decade = null): Response
    {
        return $this->render('admin/journal/issue/view.twig', [
            'area' => 'journal',
            'subarea' => 'issue',
            'decade' => $decade,
            'decades' => $issueHandler->getDecades(),
            'issues' => $issueHandler->getIssuesReversed()
        ]);
    }

    /**
     * @Route("/edit/{id}/{tab}", name="edit")
     */
    public function edit(
        Issue $issue,
        IssueHandler $issueHandler,
        Request $request,
        string $tab = 'details'
    ): Response {
        $issueForm = $this->createForm(IssueType::class, $issue);
        $issueForm->handleRequest($request);

        if ($issueForm->isSubmitted()) {
            $tab = 'details';
            if ($issueForm->isValid()) {
                $issueHandler->saveIssue($issue);
                $this->addFlash('notice', 'Issue '.$issue.' has been updated.');
                return $this->redirectToRoute('admin_journal_issue_view', ['decade' => $issue->getDecade()]);
            }
        }

        return $this->render('admin/journal/issue/edit.twig', [
            'area' => 'journal',
            'subarea' => 'issue',
            'tab' => $tab,
            'issue' => $issue,
            'issueForm' => $issueForm->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Issue $issue, IssueHandler $issueHandler, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $issueHandler->deleteIssue($issue);
            $this->addFlash('notice', 'Issue '.$issue.' has been deleted.');
            return $this->redirectToRoute('admin_journal_issue_view');
        }

        return $this->render('admin/journal/issue/delete.twig', [
            'area' => 'journal',
            'subarea' => 'issue',
            'issue' => $issue,
            'issueForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(IssueHandler $issueHandler, Request $request): Response
    {
        $issue = new Issue();
        $issueHandler->setNextVolumeAndNumber($issue);

        $issueForm = $this->createForm(IssueType::class, $issue);
        $issueForm->handleRequest($request);

        if ($issueForm->isSubmitted() && $issueForm->isValid()) {
            $issueHandler->saveIssue($issue);
            $this->addFlash('notice', 'Issue '.$issue.' has been created.');
            return $this->redirectToRoute('admin_journal_issue_view', ['decade' => $issue->getDecade()]);
        }

        return $this->render('admin/journal/issue/create.twig', [
            'area' => 'journal',
            'subarea' => 'issue',
            'issueForm' => $issueForm->createView()
        ]);
    }
}
