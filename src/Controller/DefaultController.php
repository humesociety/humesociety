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
    public function index(
        ConferenceHandler $conferenceHandler,
        NewsItemHandler $newsItemHandler
    ): Response {
        return $this->render('site/home/index.twig', [
            'page' => ['id' => 'home', 'section' => 'home'],
            'newsItems' => $newsItemHandler->getCurrentNewsItems('society'),
            'conference' => $conferenceHandler->getCurrentConference()
        ]);
    }

    /**
     * Any other page from the database.
     *
     * @param CandidateHandler The candidate handler.
     * @param ConferenceHandler The conference handler.
     * @param ElectionHandler The election handler.
     * @param NewsItemHandler The news item handler.
     * @param PageHandler The page handler.
     * @param UploadHandler The upload handler.
     * @param UserHandler The user handler.
     * @param Request The Symfony HTTP request object.
     * @param string The section of the site.
     * @param string The page within the section.
     * @return Response
     * @Route(
     *     "/{section}/{slug}",
     *     name="society_page",
     *     requirements={"section": "about|conferences|hs|scholarship|members"}
     * )
     */
    public function page(
        CandidateHandler $candidateHandler,
        ConferenceHandler $conferenceHandler,
        NewsItemHandler $newsItemHandler,
        PageHandler $pageHandler,
        UploadHandler $uploadHandler,
        Request $request,
        string $section,
        string $slug = 'index'
    ) : Response {
        // look for the page (and its siblings for the side menu)
        $page = $pageHandler->getPage($section, $slug);
        $siblings = $pageHandler->getSectionPages($section);

        // 404 error if the page doesn't exist
        if (!$page) {
            throw $this->createNotFoundException('Page not found.');
        }

        // security check for members area
        if ($section == 'members') {
            // not logged in; show page inviting to join or log in
            if (!$this->getUser()) {
                return $this->render('site/templates/members-not-logged-in.twig', [
                    'page' => $page,
                    'siblings' => $siblings
                ]);
            }
            // logged in but not a member; show page inviting to join
            if (!$this->getUser()->isMember()) {
                return $this->render('site/templates/members-not-a-member.twig', [
                    'page' => $page,
                    'siblings' => $siblings
                ]);
            }
            // member, but not in good standing; show page asking to pay dues
            if (!$this->getUser()->isMemberInGoodStanding()) {
                return $this->render('site/templates/members-lapsed.twig', [
                    'page' => $page,
                    'siblings' => $siblings
                ]);
            }
        }

        // render the page
        return $this->renderPage(
            $page,
            $siblings,
            $candidateHandler,
            $conferenceHandler,
            $newsItemHandler,
            $uploadHandler
        );
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
     *     condition="'%kernel.environment%' !== 'prod'"
     * )
     */
    public function template(
        CandidateHandler $candidateHandler,
        ConferenceHandler $conferenceHandler,
        NewsItemHandler $newsItemHandler,
        UploadHandler $uploadHandler,
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

          // return the response
          return $this->renderPage(
              $page,
              [$page],
              $candidateHandler,
              $conferenceHandler,
              $newsItemHandler,
              $uploadHandler
          );
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
        CandidateHandler $candidateHandler,
        ConferenceHandler $conferenceHandler,
        NewsItemHandler $newsItemHandler,
        UploadHandler $uploadHandler
    ): Response {
        switch ($page->getTemplate()) {
            case 'society-governance':
                return $this->render('site/templates/society-governance.twig', [
                    'page' => $page,
                    'siblings' => $siblings,
                    'years' => $candidateHandler->getYears(),
                    'evpts' => $candidateHandler->getEVPTs(),
                    'execs' => $candidateHandler->getExecs()
                ]);

            case 'conferences-forthcoming':
                return $this->render('site/templates/conferences-forthcoming.twig', [
                    'page' => $page,
                    'siblings' => $siblings,
                    'conferences' => $conferenceHandler->getForthcomingConferences()
                ]);

            case 'conferences-all':
                return $this->render('site/templates/conferences-all.twig', [
                    'page' => $page,
                    'siblings' => $siblings,
                    'decades' => $conferenceHandler->getDecades(),
                    'conferences' => $conferenceHandler->getConferences()
                ]);

            case 'news-members': // fallthrough
            case 'news-conferences': // fallthrough
            case 'news-fellowships':
                $category = explode('-', $page->getTemplate())[1];
                return $this->render('site/templates/news-current.twig', [
                    'page' => $page,
                    'siblings' => $siblings,
                    'category' => $category,
                    'newsItems' => $newsItemHandler->getCurrentNewsItems($category)
                ]);

            case 'news-archived':
                return $this->render('site/templates/news-archived.twig', [
                    'page' => $page,
                    'siblings' => $siblings,
                    'years' => $newsItemHandler->getYears(),
                    'societyNewsItems' => $newsItemHandler->getArchivedNewsItems('society'),
                    'membersNewsItems' => $newsItemHandler->getArchivedNewsItems('members'),
                    'conferencesNewsItems' => $newsItemHandler->getArchivedNewsItems('conferences'),
                    'fellowshipsNewsItems' => $newsItemHandler->getArchivedNewsItems('fellowships')
                ]);

            case 'minutes-reports':
                return $this->render('site/templates/minutes-reports.twig', [
                    'page' => $page,
                    'siblings' => $siblings,
                    'years' => $uploadHandler->getReportYears(),
                    'reports' => $uploadHandler->getReports()
                ]);

            case 'committee-voting':
                return $this->render('site/templates/committee-voting.twig', [
                    'page' => $page,
                    'siblings' => $siblings
                ]);

            default:
                return $this->render('site/templates/'.$page->getTemplate().'.twig', [
                    'page' => $page,
                    'siblings' => $siblings
                ]);
        }
    }
}
