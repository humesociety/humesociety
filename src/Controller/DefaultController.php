<?php

namespace App\Controller;

use App\Entity\Candidate\CandidateHandler;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\NewsItem\NewsItemHandler;
use App\Entity\Page\Page;
use App\Entity\Page\PageHandler;
use App\Entity\Upload\UploadHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The main controller for the site; returns the home page and any custom page from the database.
 *
 * While pages are stored in the database (and are editable by users with the appropriate
 * permissions), the main sections are hardwired (otherwise the route matching would be too
 * general, and wouldn't work well). To edit the sections, change the requirements on the page
 * method in this controller, and edit the corresponding parameters in `/services.yaml`. N.B. Every
 * section expects an `index` page in the database, to display by default. If one is not
 * found, the base route for that section will return a 404 error.
 */
class DefaultController extends AbstractController
{
    /**
     * The web site home page.
     *
     * @param ConferenceHandler The conference handler.
     * @param NewsItemHandler The news item handler.
     * @return Response
     * @Route("/", name="society_index")
     */
    public function index(ConferenceHandler $conferences, NewsItemHandler $newsItems): Response
    {
        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'home', 'section' => 'home'],
            'newsItems' => $newsItems->getCurrentNewsItems('society'),
            'conference' => $conferences->getCurrentConference()
        ];

        // render and return the page
        return $this->render('site/home/index.twig', $twigs);
    }

    /**
     * Any other page from the database.
     *
     * @param Request Symfony's request object.
     * @param CandidateHandler The candidate handler.
     * @param ConferenceHandler The conference handler.
     * @param NewsItemHandler The news item handler.
     * @param PageHandler The page handler.
     * @param UploadHandler The upload handler.
     * @param string The section of the site.
     * @param string The page within the section.
     * @return Response
     * @Route("/{section}/{slug}", name="society_page", requirements={"section": "%section_ids%"})
     */
    public function page(
        Request $request,
        CandidateHandler $candidates,
        ConferenceHandler $conferences,
        NewsItemHandler $newsItems,
        PageHandler $pages,
        UploadHandler $uploads,
        string $section,
        string $slug = 'index'
    ): Response {
        // look for the page (and its siblings for the side menu)
        $page = $pages->getPage($section, $slug);
        $siblings = $pages->getSectionPages($section);

        // 404 error if the page doesn't exist
        if (!$page) {
            throw $this->createNotFoundException('Page not found.');
        }

        // initialise the twig variables
        $twigs = ['page' => $page, 'siblings' => $siblings];

        // security check for members area
        if ($section === 'members') {
            // not logged in; show page inviting to join or log in
            if (!$this->getUser()) {
                return $this->render('site/templates/members-not-logged-in.twig', $twigs);
            }
            // logged in but not a member; show page inviting to join
            if (!$this->getUser()->isMember()) {
                return $this->render('site/templates/members-not-a-member.twig', $twigs);
            }
            // member, but not in good standing; show page asking to pay dues
            if (!$this->getUser()->isMemberInGoodStanding()) {
                return $this->render('site/templates/members-lapsed.twig', $twigs);
            }
        }

        // render and return the page
        return $this->renderPage($page, $siblings, $candidates, $conferences, $newsItems, $uploads);
    }

    /**
     * Dummy template pages (used for testing the templates). Not to be made available in production.
     *
     * @param CandidateHandler The candidate handler.
     * @param ConferenceHandler The conference handler.
     * @param NewsItemHandler The news item handler.
     * @param UploadHandler The upload handler.
     * @param string The template to render.
     * @return Response
     * @Route(
     *     "/template/{template}",
     *     name="society_template",
     *     requirements={"template": "%page_template_ids%"},
     *     condition="'%kernel.environment%' !== 'prod'"
     * )
     */
    public function template(
        CandidateHandler $candidates,
        ConferenceHandler $conferences,
        NewsItemHandler $newsItems,
        UploadHandler $uploads,
        string $template
    ): Response {
          // create a dummy page with the given template
          $page = new Page();
          $page->setSection('about')
              ->setPosition(1)
              ->setSlug($template)
              ->setTitle($template)
              ->setTemplate($template)
              ->setContent('<p>Example of this template.</p>');

          // render and return the response
          return $this->renderPage($page, [$page], $candidates, $conferences, $newsItems, $uploads);
    }

    /**
     * Function for rendering a given page.
     *
     * @param Page The page to render.
     * @param Page[] The page's siblings.
     * @param CandidateHandler The candidate handler.
     * @param ConferenceHandler The conference handler.
     * @param NewsItemHandler The news item handler.
     * @param UploadHandler The upload handler.
     * @return Response
     */
    private function renderPage(
        Page $page,
        array $siblings,
        CandidateHandler $candidates,
        ConferenceHandler $conferences,
        NewsItemHandler $newsItems,
        UploadHandler $uploads
    ): Response {
        // initialise the twig variables
        $twigs = ['page' => $page, 'siblings' => $siblings];
        switch ($page->getTemplate()) {
            case 'society-governance':
                $twigs['years'] = $candidates->getYears();
                $twigs['evpts'] = $candidates->getEVPTs();
                $twigs['execs'] = $candidates->getExecs();
                return $this->render('site/templates/society-governance.twig', $twigs);

            case 'conferences-forthcoming':
                $twigs['conferences'] = $conferences->getForthcomingConferences();
                return $this->render('site/templates/conferences-forthcoming.twig', $twigs);

            case 'conferences-all':
                $twigs['decades'] = $conferences->getDecades();
                $twigs['conferences'] = $conferences->getConferences();
                return $this->render('site/templates/conferences-all.twig', $twigs);

            case 'news-members': // fallthrough
            case 'news-conferences': // fallthrough
            case 'news-fellowships': // fallthrough
            case 'news-jobs':
                $twigs['category'] = explode('-', $page->getTemplate())[1];
                $twigs['newsItems'] = $newsItems->getCurrentNewsItems($twigs['category']);
                return $this->render('site/templates/news-current.twig', $twigs);

            case 'news-archived':
                $twigs['years'] = $newsItems->getYears();
                $twigs['societyNewsItems'] = $newsItems->getArchivedNewsItems('society');
                $twigs['membersNewsItems'] = $newsItems->getArchivedNewsItems('members');
                $twigs['conferencesNewsItems'] = $newsItems->getArchivedNewsItems('conferences');
                $twigs['fellowshipsNewsItems'] = $newsItems->getArchivedNewsItems('fellowships');
                return $this->render('site/templates/news-archived.twig', $twigs);

            case 'minutes-reports':
                $twigs['years'] = $uploads->getReportYears();
                $twigs['reports'] = $uploads->getReports();
                return $this->render('site/templates/minutes-reports.twig', $twigs);

            case 'committee-voting':
                // TODO: voting form
                return $this->render('site/templates/committee-voting.twig', $twigs);

            default:
                return $this->render("site/templates/{$page->getTemplate()}.twig", $twigs);
        }
    }
}
