<?php

namespace App\Controller\Admin\Content;

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
 * @Route("/admin/content/page", name="admin_content_page_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for editing the pages of the main site.
 */
class PageController extends AbstractController
{
    /**
     * @Route("/{section}", name="index", requirements={"section": "%section_ids%"})
     */
    public function index(PageHandler $pageHandler, $section = 'about'): Response
    {
        return $this->render('admin/content/page/view.twig', [
            'area' => 'content',
            'subarea' => 'page',
            'section' => $section,
            'pages' => $pageHandler->getPages()
        ]);
    }

    /**
     * @Route("/up/{id}", name="up")
     */
    public function up(Page $page, PageHandler $pageHandler): Response
    {
        $pageHandler->movePageUp($page);
        $this->addFlash('notice', 'Page "'.$page.'" has been moved up.');
        return $this->redirectToRoute('admin_content_page_index', ['section' => $page->getSection()]);
    }

    /**
     * @Route("/down/{id}", name="down")
     */
    public function down(Page $page, PageHandler $pageHandler): Response
    {
        $pageHandler->movePageDown($page);
        $this->addFlash('notice', 'Page "'.$page.'" has been moved down.');
        return $this->redirectToRoute('admin_content_page_index', ['section' => $page->getSection()]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Page $page, PageHandler $pageHandler, Request $request): Response
    {
        $isIndexPage = ($page->getSlug() === 'index');
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isIndexPage && ($page->getSlug() != 'index')) {
                $error = new FormError('The slug for index pages cannot be changed.');
                $form->get('slug')->addError($error);
            } else {
                $pageHandler->savePage($page);
                $this->addFlash('notice', 'Page "'.$page.'" has been updated.');
                return $this->redirectToRoute('admin_content_page_index', ['section' => $page->getSection()]);
            }
        }

        return $this->render('admin/content/page/edit.twig', [
            'area' => 'content',
            'subarea' => 'page',
            'page' => $page,
            'pageForm' => $form->createView(),
            'formName' => $form->getName()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Page $page, PageHandler $pageHandler, Request $request) : Response
    {
        if ($page->getSlug() === 'index') {
            throw $this->createNotFoundException(); // index pages cannot be deleted
        }

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $pageHandler->deletePage($page);
            $this->addFlash('notice', 'Page "'.$page.'" has been deleted.');
            return $this->redirectToRoute('admin_content_page_index', ['section' => $page->getSection()]);
        }

        return $this->render('admin/content/page/delete.twig', [
            'area' => 'content',
            'subarea' => 'page',
            'page' => $page,
            'deleteForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/create/{section}", name="create")
     */
    public function create(PageHandler $pageHandler, Request $request, string $section = 'about'): Response
    {
        $page = new Page();
        $page->setSection($section);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->setPosition($pageHandler->getNextPagePosition($page->getSection()));
            $pageHandler->savePage($page);
            $this->addFlash('notice', 'Page "'.$page.'" has been created.');
            return $this->redirectToRoute('admin_content_page_index', ['section' => $page->getSection()]);
        }

        return $this->render('admin/content/page/create.twig', [
            'area' => 'content',
            'subarea' => 'page',
            'pageForm' => $form->createView(),
            'formName' => $form->getName()
        ]);
    }
}
