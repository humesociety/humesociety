<?php

namespace App\Controller\Journal;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleHandler;
use App\Entity\Article\ArticleTypeCreate;
use App\Entity\Article\ArticleTypeEdit;
use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueHandler;
use App\Entity\Issue\IssueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing Hume Studies issues.
 *
 * @Route("/journal-manager/issue", name="journal_issue_")
 * @IsGranted("ROLE_EDITOR")
 */
class IssueController extends AbstractController
{
    /**
     * Route for viewing all issues.
     *
     * @param IssueHandler $issues The issue handler.
     * @param string|null $decade The decade of issues to show.
     * @return Response
     * @Route("/{decade}", name="index", requirements={"decade": "\d{4}"})
     */
    public function view(IssueHandler $issues, string $decade = null): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'issue',
            'decade' => $decade,
            'decades' => $issues->getDecades(),
            'issues' => $issues->getIssuesReversed()
        ];

        // render and return the page
        return $this->render('journal/issue/view.twig', $twigs);
    }

    /**
     * Route for creating an issue.
     *
     * @param Request $request Symfony's request object.
     * @param IssueHandler $issues The issue handler.
     * @return Response
     * @Route("/create", name="create")
     */
    public function create(Request $request, IssueHandler $issues): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'issue',
        ];

        // create and handle the new issue form
        $issue = $issues->createNextIssue();
        $issueForm = $this->createForm(IssueType::class, $issue);
        $issueForm->handleRequest($request);
        if ($issueForm->isSubmitted() && $issueForm->isValid()) {
            $issues->saveIssue($issue);
            $this->addFlash('notice', "Issue {$issue} has been created.");
            return $this->redirectToRoute('journal_issue_index', ['decade' => $issue->getDecade()]);
        }

        // add additional twig variables
        $twigs['issueForm'] = $issueForm->createView();

        // render and return the page
        return $this->render('journal/issue/create.twig', $twigs);
    }

    /**
     * Route for editing an issue.
     *
     * @param Request $request Symfony's request object.
     * @param IssueHandler $issues The issue handler.
     * @param Issue $issue The issue to edit.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/edit/{id}/{tab}", name="edit", requirements={"tab"="details|articles"})
     */
    public function edit(Request $request, IssueHandler $issues, Issue $issue, string $tab = 'details'): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'issue',
            'tab' => $tab,
            'issue' => $issue,
        ];

        // create and handle the edit issue form
        $issueForm = $this->createForm(IssueType::class, $issue);
        $issueForm->handleRequest($request);
        if ($issueForm->isSubmitted()) {
            $tab = 'details';
            if ($issueForm->isValid()) {
                $issues->saveIssue($issue);
                $this->addFlash('notice', "Issue {$issue} has been updated.");
                return $this->redirectToRoute('journal_issue_index', ['decade' => $issue->getDecade()]);
            }
        }

        // add additional twig variables
        $twigs['issueForm'] = $issueForm->createView();

        // render and return the page
        return $this->render('journal/issue/edit.twig', $twigs);
    }

    /**
     * Route for creating an article in an issue.
     *
     * @param Request $request Symfony's request object.
     * @param ArticleHandler $articles The article handler.
     * @param Issue $issue The issue.
     * @return Response
     * @Route("/edit/{id}/create-article", name="create_article")
     */
    public function createArticle(Request $request, ArticleHandler $articles, Issue $issue): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'issue',
            'issue' => $issue,
        ];

        // create and handle the article form
        $article = $articles->createNextArticle($issue);
        $articleForm = $this->createForm(ArticleTypeCreate::class, $article);
        $articleForm->handleRequest($request);
        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $articles->saveArticle($article);
            $this->addFlash('notice', "\"{$article}\" has been created.");
            return $this->redirectToRoute('journal_issue_edit', [
                'id' => $issue->getId(),
                'tab' => 'articles'
            ]);
        }

        // add additional twig variables
        $twigs['articleForm'] = $articleForm->createView();

        // render and return the page
        return $this->render('journal/article/create.twig', $twigs);
    }

    /**
     * Route for editing an article in an issue.
     *
     * @param Request $request Symfony's request object.
     * @param ArticleHandler $articles The article handler.
     * @param Article $article The article.
     * @return Response
     * @Route("/edit/edit-article/{id}", name="edit_article")
     */
    public function editArticle(Request $request, ArticleHandler $articles, Article $article): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'issue',
            'article' => $article,
        ];

        // remember the current filename in case it changes
        $oldFilename = $article->getFilename();

        // create and handle the edit article form
        $articleForm = $this->createForm(ArticleTypeEdit::class, $article);
        $articleForm->handleRequest($request);
        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $articles->saveArticle($article);
            if ($article->getFilename() !== $oldFilename) {
                $articles->renameArticleFile($article, $oldFilename);
            }
            $this->addFlash('notice', "\"{$article}\" has been updated.");
            return $this->redirectToRoute('journal_issue_edit', [
                'id' => $article->getIssue()->getId(),
                'tab' => 'articles'
            ]);
        }

        // add additional twig variables
        $twigs['articleForm'] = $articleForm->createView();

        // render and return the page
        return $this->render('journal/article/edit.twig', $twigs);
    }

    /**
     * Route for moving an article up in an issue.
     *
     * @param ArticleHandler $articles The article handler.
     * @param Article $article The article to move.
     * @return Response
     * @Route("/edit/move-article-up/{id}", name="move_article_up")
     */
    public function moveArticleUp(ArticleHandler $articles, Article $article): Response
    {
        // move the article up
        $articles->moveArticleUp($article);

        // redirect to the issue edit page
        return $this->redirectToRoute('journal_issue_edit', [
            'id' => $article->getIssue()->getId(),
            'tab' => 'articles'
        ]);
    }

    /**
     * Route for moving an article down in an issue.
     *
     * @param ArticleHandler $articles The article handler.
     * @param Article $article The article to move.
     * @return Response
     * @Route("/edit/move-article-down/{id}", name="move_article_down")
     */
    public function moveArticleDown(ArticleHandler $articles, Article $article): Response
    {
        // move the article
        $articles->moveArticleDown($article);

        // redirect to the issue edit page
        return $this->redirectToRoute('journal_issue_edit', [
            'id' => $article->getIssue()->getId(),
            'tab' => 'articles'
        ]);
    }

    /**
     * Route for deleting an article.
     *
     * @param Request $request Symfony's request object.
     * @param ArticleHandler $articles The article handler.
     * @param Article $article The article to delete.
     * @return Response
     * @Route("/edit/delete-article/{id}", name="delete_article")
     */
    public function deleteArticle(Request $request, ArticleHandler $articles, Article $article): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'issue',
            'article' => $article
        ];

        // create and handle the delete article form
        $deleteArticleForm = $this->createFormBuilder()->getForm();
        $deleteArticleForm->handleRequest($request);
        if ($deleteArticleForm->isSubmitted()) {
            $articles->deleteArticle($article);
            $this->addFlash('notice', "\"{$article}\" has been deleted.");
            return $this->redirectToRoute('journal_issue_edit', [
                'id' => $article->getIssue()->getId(),
                'tab' => 'articles'
            ]);
        }

        // add aditional twig variables
        $twigs['deleteArticleForm'] = $deleteArticleForm->createView();

        // return the response
        return $this->render('journal/article/delete.twig', $twigs);
    }

    /**
     * Route for deleting an issue.
     *
     * @param Request $request Symfony's request object.
     * @param IssueHandler $issues The issue handler.
     * @param Issue $issue The issue to delete.
     * @return Response
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Request $request, IssueHandler $issues, Issue $issue): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'issue',
            'issue' => $issue,
        ];

        // create and handle the delete issue form
        $deleteIssueForm = $this->createFormBuilder()->getForm();
        $deleteIssueForm->handleRequest($request);
        if ($deleteIssueForm->isSubmitted() && $deleteIssueForm->isValid()) {
            $issues->deleteIssue($issue);
            $this->addFlash('notice', "Issue {$issue} has been deleted.");
            return $this->redirectToRoute('journal_issue_index');
        }

        // add additional twig variables
        $twigs['deleteIssueForm'] = $deleteIssueForm->createView();

        // render and return the page
        return $this->render('journal/issue/delete.twig', $twigs);
    }
}
