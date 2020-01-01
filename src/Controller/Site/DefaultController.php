<?php

namespace App\Controller\Site;

use App\Entity\Candidate\CandidateHandler;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Election\ElectionHandler;
use App\Entity\Election\VotingType;
use App\Entity\Election\VotingRunOffType;
use App\Entity\NewsItem\NewsItemHandler;
use App\Entity\Page\Page;
use App\Entity\Page\PageHandler;
use App\Entity\Upload\UploadHandler;
use App\Entity\User\UserHandler;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
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
     * @param ConferenceHandler $conferences The conference handler.
     * @param NewsItemHandler $newsItems The news item handler.
     * @return Response
     * @Route("/", name="society_index")
     * @throws Exception
     */
    public function index(ConferenceHandler $conferences, NewsItemHandler $newsItems): Response
    {
        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'home', 'section' => 'home', 'title' => 'The Hume Society'],
            'newsItems' => $newsItems->getCurrentNewsItems('society'),
            'conference' => $conferences->getCurrentConference()
        ];

        // render and return the page
        return $this->render('site/home/index.twig', $twigs);
    }

    /**
     * Any other page from the database.
     *
     * @param Request $request Symfony's request object.
     * @param CandidateHandler $candidates The candidate handler.
     * @param ConferenceHandler $conferences The conference handler.
     * @param ElectionHandler $elections
     * @param NewsItemHandler $newsItems The news item handler.
     * @param PageHandler $pages The page handler.
     * @param UploadHandler $uploads The upload handler.
     * @param UserHandler $users
     * @param string $section The section of the site.
     * @param string $slug The page within the section.
     * @return Response
     * @throws NonUniqueResultException
     * @Route("/{section}/{slug}", name="society_page", requirements={"section": "%section_ids%"})
     */
    public function page(
        Request $request,
        CandidateHandler $candidates,
        ConferenceHandler $conferences,
        ElectionHandler $elections,
        NewsItemHandler $newsItems,
        PageHandler $pages,
        UploadHandler $uploads,
        UserHandler $users,
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
        return $this->renderPage($request, $page, $siblings, $candidates, $conferences, $elections, $newsItems, $uploads, $users);
    }

    /**
     * Dummy template pages (used for testing the templates). Not to be made available in production.
     *
     * @param Request $request
     * @param CandidateHandler $candidates The candidate handler.
     * @param ConferenceHandler $conferences The conference handler.
     * @param ElectionHandler $elections
     * @param NewsItemHandler $newsItems The news item handler.
     * @param UploadHandler $uploads The upload handler.
     * @param UserHandler $users
     * @param string $template The template to render.
     * @return Response
     * @throws NonUniqueResultException
     * @Route(
     *     "/template/{template}",
     *     name="society_template",
     *     requirements={"template": "%page_template_ids%"},
     *     condition="'%kernel.environment%' !== 'prod'"
     * )
     */
    public function template(
        Request $request,
        CandidateHandler $candidates,
        ConferenceHandler $conferences,
        ElectionHandler $elections,
        NewsItemHandler $newsItems,
        UploadHandler $uploads,
        UserHandler $users,
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
          return $this->renderPage($request, $page, [$page], $candidates, $conferences, $elections, $newsItems, $uploads, $users);
    }

    /**
     * Function for rendering a given page.
     *
     * @param Request $request
     * @param Page $page The page to render.
     * @param Page[] $siblings The page's siblings.
     * @param CandidateHandler $candidates The candidate handler.
     * @param ConferenceHandler $conferences The conference handler.
     * @param ElectionHandler $elections
     * @param NewsItemHandler $newsItems The news item handler.
     * @param UploadHandler $uploads The upload handler.
     * @param UserHandler $users
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    private function renderPage(
        Request $request,
        Page $page,
        array $siblings,
        CandidateHandler $candidates,
        ConferenceHandler $conferences,
        ElectionHandler $elections,
        NewsItemHandler $newsItems,
        UploadHandler $uploads,
        UserHandler $users
    ): Response {
        // initialise the twig variables
        $twigs = ['page' => $page, 'siblings' => $siblings];
        switch ($page->getTemplate()) {
            case 'society-governance':
                $twigs['years'] = $candidates->getYears();
                $twigs['evpts'] = $candidates->getEvpts();
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
                $election = $elections->getOpenElection();
                if ($election && !$this->getUser()->hasVoted()) {
                    $electionCandidates = $candidates->getCandidatesByYear($election->getYear());
                    $ordinary = 3;
                    $presidentStanding = false;
                    foreach ($electionCandidates as $candidate) {
                        if ($candidate->getPresident()) {
                            $presidentStanding = true;
                        }
                    }
                    if ($presidentStanding) {
                        $ordinary -= 1;
                    }
                    $votingForm = $this->createForm(VotingType::class);
                    $votingForm->handleRequest($request);
                    if ($votingForm->isSubmitted() && $votingForm->isValid()) {
                        foreach ($electionCandidates as $candidate) {
                            if ($votingForm->getData()[$candidate->getId()] === true) {
                                $candidate->incrementVotes();
                                $candidates->saveCandidate($candidate);
                            }
                        }
                        $this->getUser()->setVoted(true);
                        $users->saveUser($this->getUser());
                        $election->incrementVotes();
                        $elections->saveElection($election);
                    }
                    $twigs['candidates'] = $electionCandidates;
                    $twigs['ordinary'] = $ordinary;
                    $twigs['votingForm'] = $votingForm->createView();
                }
                $electionRunOff = $elections->getOpenElectionRunOff();
                if ($electionRunOff && !$this->getUser()->hasVoted()) {
                    $electionCandidates = $candidates->getRunOffCandidatesByYear($electionRunOff->getYear());
                    $votingRunOffForm = $this->createForm(VotingRunOffType::class);
                    $votingRunOffForm->handleRequest($request);
                    if ($votingRunOffForm->isSubmitted() && $votingRunOffForm->isValid()) {
                        foreach ($electionCandidates as $candidate) {
                            if ($votingRunOffForm->getData()[$candidate->getId()] === true) {
                                $candidate->incrementRunOffVotes();
                                $candidates->saveCandidate($candidate);
                            }
                        }
                        $this->getUser()->setVoted(true);
                        $users->saveUser($this->getUser());
                        $electionRunOff->incrementRunOffVotes();
                        $elections->saveElection($electionRunOff);
                    }
                    $twigs['candidates'] = $electionCandidates;
                    $twigs['ordinary'] = 1;
                    $twigs['votingForm'] = $votingRunOffForm->createView();
                }
                $twigs['election'] = $election ? $election : null;
                $twigs['election'] = $electionRunOff ? $electionRunOff : null;
                return $this->render('site/templates/committee-voting.twig', $twigs);

            default:
                return $this->render("site/templates/{$page->getTemplate()}.twig", $twigs);
        }
    }
}
