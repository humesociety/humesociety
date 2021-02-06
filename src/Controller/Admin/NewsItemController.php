<?php

namespace App\Controller\Admin;

use App\Entity\NewsItem\NewsItem;
use App\Entity\NewsItem\NewsItemType;
use App\Entity\NewsItem\NewsItemHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for editing society and membership news items.
 *
 * @Route("/admin/news-item", name="admin_news-item_")
 * @IsGranted("ROLE_EVPT")
 */
class NewsItemController extends AbstractController
{
    /**
     * Route for viewing news items.
     *
     * @param NewsItemHandler $newsItems The news item handler.
     * @param string $category The initially visible category.
     * @return Response
     * @Route("/{category}", name="index", requirements={"category": "%news_category_ids%"})
     */
    public function index(NewsItemHandler $newsItems, $category = 'society'): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'news-item',
            'category' => $category,
            'newsItems' => $newsItems->getNewsItems()
        ];

        // render and return the page
        return $this->render('admin/news-item/view.twig', $twigs);
    }

    /**
     * Route for creating a news item.
     *
     * @param Request $request Symfony's request object.
     * @param NewsItemHandler $newsItems The news item handler.
     * @param string $category The initial category to set.
     * @return Response
     * @Route("/create/{category}", name="create", requirements={"category": "%news_category_ids%"})
     */
    public function create(Request $request, NewsItemHandler $newsItems, $category = 'society'): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'news-item'
        ];

        // create and handle the news item form
        $newsItem = new NewsItem($category);
        $newsItemForm = $this->createForm(NewsItemType::class, $newsItem);
        $newsItemForm->handleRequest($request);
        if ($newsItemForm->isSubmitted() && $newsItemForm->isValid()) {
            $newsItems->saveNewsItem($newsItem);
            $this->addFlash('notice', 'News item "'.$newsItem.'" has been created.');
            return $this->redirectToRoute('admin_news-item_index', ['category' => $newsItem->getCategory()]);
        }

        // add additional twig variables
        $twigs['newsItemForm'] = $newsItemForm->createView();

        // render and return the page
        return $this->render('admin/news-item/create.twig', $twigs);
    }

    /**
     * Route for editing a news item.
     *
     * @param Request $request Symfony's request object.
     * @param NewsItemHandler $newsItems The news item handler.
     * @param NewsItem $newsItem The news item to edit.
     * @return Response
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Request $request, NewsItemHandler $newsItems, NewsItem $newsItem) : Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'news-item',
            'newsItem' => $newsItem
        ];

        // create and handle the news item form
        $newsItemForm = $this->createForm(NewsItemType::class, $newsItem);
        $newsItemForm->handleRequest($request);
        if ($newsItemForm->isSubmitted() && $newsItemForm->isValid()) {
            $newsItems->saveNewsItem($newsItem);
            $this->addFlash('notice', 'News item "'.$newsItem.'" has been updated.');
            return $this->redirectToRoute('admin_news-item_index', ['category' => $newsItem->getCategory()]);
        }

        // add additional twig variables
        $twigs['newsItemForm'] = $newsItemForm->createView();

        // render and return the page
        return $this->render('admin/news-item/edit.twig', $twigs);
    }

    /**
     * Route for deleting a news item.
     *
     * @param Request $request Symfony's request object.
     * @param NewsItemHandler $newsItems The news item handler.
     * @param NewsItem $newsItem The news item to delete.
     * @return Response
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Request $request, NewsItemHandler $newsItems, NewsItem $newsItem): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'news-item',
            'newsItem' => $newsItem
        ];

        // create and handle the delete news item form
        $newsItemForm = $this->createFormBuilder()->getForm();
        $newsItemForm->handleRequest($request);
        if ($newsItemForm->isSubmitted() && $newsItemForm->isValid()) {
            $newsItems->deleteNewsItem($newsItem);
            $this->addFlash('notice', 'News item "'.$newsItem.' has been deleted.');
            return $this->redirectToRoute('admin_news-item_index', ['category' => $newsItem->getCategory()]);
        }

        // add additional twig variables
        $twigs['newsItemForm'] = $newsItemForm->createView();

        // render and return the page
        return $this->render('admin/news-item/delete.twig', $twigs);
    }
}
