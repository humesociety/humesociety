<?php

namespace App\Controller\Admin;

use App\Entity\Page\Page;
use App\Entity\Page\PageHandler;
use App\Entity\Page\PageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/page", name="admin_page_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for editing the pages of the main site.
 */
class PageController extends AbstractController
{
    /**
     * Route for viewing pages.
     *
     * @param PageHandler $pages The page handler.
     * @param string $section The initial section to show.
     * @return Response
     * @Route("/{section}", name="index", requirements={"section": "%section_ids%"})
     */
    public function index(PageHandler $pages, $section = 'about'): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'page',
            'section' => $section,
            'pages' => $pages->getPages()
        ];

        // render and return the page
        return $this->render('admin/page/view.twig', $twigs);
    }

    /**
     * Route for creating a page.
     *
     * @param Request $request Symfony's request object.
     * @param PageHandler $pages The page handler.
     * @param string $section The initial section to set.
     * @return Response
     * @Route("/create/{section}", name="create")
     */
    public function create(Request $request, PageHandler $pages, string $section = 'about'): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'page'
        ];

        // create and handle the page form
        $page = $pages->createNextPage($section);
        $pageForm = $this->createForm(PageType::class, $page);
        $pageForm->handleRequest($request);
        if ($pageForm->isSubmitted() && $pageForm->isValid()) {
            $pages->savePage($page);
            $this->addFlash('notice', 'Page "'.$page.'" has been created.');
            return $this->redirectToRoute('admin_page_index', ['section' => $page->getSection()]);
        }

        // add additional twig variables
        $twigs['pageForm'] = $pageForm->createView();
        $twigs['formName'] = $pageForm->getName();

        // render and return the page
        return $this->render('admin/page/create.twig', $twigs);
    }

    /**
     * Route for moving a page up in its section.
     *
     * @param PageHandler $pages The page handler.
     * @param Page $page The page to move.
     * @return Response
     * @Route("/up/{id}", name="up")
     */
    public function up(PageHandler $pages, Page $page): Response
    {
        $pages->movePageUp($page);
        $this->addFlash('notice', 'Page "'.$page.'" has been moved up.');
        return $this->redirectToRoute('admin_page_index', ['section' => $page->getSection()]);
    }

    /**
     * Route for moving a page down in its section.
     *
     * @param PageHandler $pages The page handler.
     * @param Page $page The page to move.
     * @return Response
     * @Route("/down/{id}", name="down")
     */
    public function down(PageHandler $pages, Page $page): Response
    {
        $pages->movePageDown($page);
        $this->addFlash('notice', 'Page "'.$page.'" has been moved down.');
        return $this->redirectToRoute('admin_page_index', ['section' => $page->getSection()]);
    }

    /**
     * Route for editing a page.
     *
     * @param Request $request Symfony's request object.
     * @param PageHandler $pages The page handler.
     * @param Page $page The page to edit.
     * @return Response
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Request $request, PageHandler $pages, Page $page): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'page',
            'page' => $page
        ];

        // create and handle the page form
        $isIndexPage = ($page->getSlug() === 'index');
        $pageForm = $this->createForm(PageType::class, $page);
        $pageForm->handleRequest($request);
        if ($pageForm->isSubmitted() && $pageForm->isValid()) {
            if ($isIndexPage && ($page->getSlug() !== 'index')) {
                $error = new FormError('The slug for index pages cannot be changed.');
                $pageForm->get('slug')->addError($error);
            } else {
                $pages->savePage($page);
                $this->addFlash('notice', 'Page "'.$page.'" has been updated.');
                return $this->redirectToRoute('admin_page_index', ['section' => $page->getSection()]);
            }
        }

        // add additional twig variables
        $twigs['pageForm'] = $pageForm->createView();
        $twigs['formName'] = $pageForm->getName();

        // render and return the page
        return $this->render('admin/page/edit.twig', $twigs);
    }

    /**
     * Route for deleting a page.
     *
     * @param Request $request Symfony's request object.
     * @param PageHandler $pages The page handler.
     * @param Page $page The page to delete.
     * @return Response
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Request $request, PageHandler $pages, Page $page) : Response
    {
        // don't allow index pages to be deleted
        if ($page->getSlug() === 'index') {
            throw $this->createNotFoundException(); // index pages cannot be deleted
        }

        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'page'
        ];

        // create and handle the delete page form
        $deleteForm = $this->createFormBuilder()->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $pages->deletePage($page);
            $this->addFlash('notice', 'Page "'.$page.'" has been deleted.');
            return $this->redirectToRoute('admin_page_index', ['section' => $page->getSection()]);
        }

        // add additional twig variables
        $twigs['page'] = $page;
        $twigs['deleteForm'] = $deleteForm->createView();

        // render and return the page
        return $this->render('admin/page/delete.twig', $twigs);
    }
}
