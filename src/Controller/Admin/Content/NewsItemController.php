<?php

namespace App\Controller\Admin\Content;

use App\Entity\NewsItem\NewsItem;
use App\Entity\NewsItem\NewsItemType;
use App\Entity\NewsItem\NewsItemHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/content/news-item", name="admin_content_news-item_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for editing society and membership news items.
 */
class NewsItemController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_content_news-item_view');
    }

    /**
     * @Route("/view/{category}", name="view")
     */
    public function view(NewsItemHandler $newsItemHandler, $category = 'society'): Response
    {
        return $this->render('admin/content/news-item/view.twig', [
            'area' => 'content',
            'subarea' => 'news-item',
            'category' => $category,
            'newsItems' => $newsItemHandler->getNewsItems()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(NewsItem $newsItem, NewsItemHandler $newsItemHandler, Request $request) : Response
    {
        $newsItemForm = $this->createForm(NewsItemType::class, $newsItem);
        $newsItemForm->handleRequest($request);

        if ($newsItemForm->isSubmitted() && $newsItemForm->isValid()) {
            $newsItemHandler->saveNewsItem($newsItem);
            $this->addFlash('notice', 'News item "'.$newsItem.'" has been updated.');
            return $this->redirectToRoute('admin_content_news-item_view', ['category' => $newsItem->getCategory()]);
        }

        return $this->render('admin/content/news-item/edit.twig', [
            'area' => 'content',
            'subarea' => 'news-item',
            'newsItem' => $newsItem,
            'newsItemForm' => $newsItemForm->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(NewsItem $newsItem, NewsItemHandler $newsItemHandler, Request $request) : Response
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $newsItemHandler->deleteNewsItem($newsItem);
            $this->addFlash('notice', 'News item "'.$newsItem.' has been deleted.');
            return $this->redirectToRoute('admin_content_news-item_view', ['category' => $newsItem->getCategory()]);
        }

        return $this->render('admin/content/news-item/delete.twig', [
            'area' => 'content',
            'subarea' => 'news-item',
            'newsItem' => $newsItem,
            'newsItemForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/create/{category}", name="create")
     */
    public function create(NewsItemHandler $newsItemHandler, Request $request, $category = 'society') : Response
    {
        $newsItem = new NewsItem();
        $newsItem->setCategory($category);

        $newsItemForm = $this->createForm(NewsItemType::class, $newsItem);
        $newsItemForm->handleRequest($request);

        if ($newsItemForm->isSubmitted() && $newsItemForm->isValid()) {
            $newsItemHandler->saveNewsItem($newsItem);
            $this->addFlash('notice', 'News item "'.$newsItem.'" has been created.');
            return $this->redirectToRoute('admin_content_news-item_view', ['category' => $newsItem->getCategory()]);
        }

        return $this->render('admin/content/news-item/create.twig', [
            'area' => 'content',
            'subarea' => 'news-item',
            'newsItemForm' => $newsItemForm->createView()
        ]);
    }
}
