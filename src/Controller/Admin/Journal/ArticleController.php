<?php

namespace App\Controller\Admin\Journal;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleHandler;
use App\Entity\Article\ArticleCreateType;
use App\Entity\Article\ArticleEditType;
use App\Entity\Issue\Issue;
use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/journal/article", name="admin_journal_article_")
 * @IsGranted("ROLE_EDITOR")
 *
 * This is the controller for managing Hume Studies articles.
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Article $article, ArticleHandler $articleHandler, Request $request): Response
    {
        $oldFilename = $article->getFilename();

        $articleForm = $this->createForm(ArticleEditType::class, $article);
        $articleForm->handleRequest($request);

        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $articleHandler->saveArticle($article);
            if ($article->getFilename() !== $oldFilename) {
                $articleHandler->renameArticleFile($article, $oldFilename);
            }
            $this->addFlash('notice', 'Article "'.$article.'" has been updated.');
            return $this->redirectToRoute('admin_journal_issue_edit', [
                'id' => $article->getIssue()->getId(),
                'tab' => 'articles'
            ]);
        }

        return $this->render('admin/journal/article/edit.twig', [
            'area' => 'journal',
            'subarea' => 'issue',
            'article' => $article,
            'articleForm' => $articleForm->createView()
        ]);
    }

    /**
     * @Route("/up/{id}", name="up")
     */
    public function up(Article $article, ArticleHandler $articleHandler): Response
    {
        $articleHandler->moveArticleUp($article);
        $this->addFlash('notice', 'Article "'.$article.'" has been moved up.');
        return $this->redirectToRoute('admin_journal_issue_edit', [
            'id' => $article->getIssue()->getId(),
            'tab' => 'articles'
        ]);
    }

    /**
     * @Route("/down/{id}", name="down")
     */
    public function down(Article $article, ArticleHandler $articleHandler): Response
    {
        $articleHandler->moveArticleDown($article);
        $this->addFlash('notice', 'Article "'.$article.'" has been moved down.');
        return $this->redirectToRoute('admin_journal_issue_edit', [
            'id' => $article->getIssue()->getId(),
            'tab' => 'articles'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Article $article, ArticleHandler $articleHandler, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $articleHandler->deleteArticle($article);
            $this->addFlash('notice', 'Article "'.$article.'" has been deleted.');
            return $this->redirectToRoute('admin_journal_issue_edit', [
                'id' => $article->getIssue()->getId(),
                'tab' => 'articles'
            ]);
        }

        return $this->render('admin/journal/article/delete.twig', [
            'area' => 'journal',
            'subarea' => 'issue',
            'article' => $article,
            'articleForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/create/{id}", name="create")
     */
    public function create(Issue $issue, ArticleHandler $articleHandler, Request $request): Response
    {
        $article = new Article();
        $article->setIssue($issue);
        $article->setPosition($articleHandler->getNextArticlePosition($issue));

        $articleForm = $this->createForm(ArticleCreateType::class, $article);
        $articleForm->handleRequest($request);

        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $articleHandler->saveArticle($article);
            $this->addFlash('notice', 'Article "'.$article.'" has been created.');
            return $this->redirectToRoute('admin_journal_issue_view', [
                'id' => $issue->getId(),
                'tab' => 'articles'
            ]);
        }

        return $this->render('admin/journal/article/create.twig', [
            'area' => 'journal',
            'subarea' => 'issue',
            'issue' => $issue,
            'articleForm' => $articleForm->createView()
        ]);
    }
}
