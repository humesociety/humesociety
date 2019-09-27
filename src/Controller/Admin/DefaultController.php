<?php

namespace App\Controller\Admin;

use App\Entity\Conference\ConferenceHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The default controller for the admin area. These routes simply redirect to the appropriate
 * place.
 *
 * Access is restricted to those with ROLE_ADMIN. This is configured for any '/admin' URL in the
 * config/packages/security.yaml, and also - you can't be too careful - with annotations here.
 *
 * @Route("/admin", name="admin_")
 */
class DefaultController extends AbstractController
{
    /**
     * The admin index page; redirect according to the user's permissions.
     *
     * @return Response
     * @Route("/", name="index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(): Response
    {
        if ($this->isGranted('ROLE_EVPT')) {
            return $this->redirectToRoute('admin_content');
        }
        if ($this->isGranted('ROLE_ORGANISER')) {
            return $this->redirectToRoute('admin_conference');
        }
        if ($this->isGranted('ROLE_EDITOR')) {
            return $this->redirectToRoute('admin_journal');
        }
        return $this->redirectToRoute('admin_content');
    }

    /**
     * The content area index page.
     *
     * @return Response
     * @Route("/content", name="content")
     * @IsGranted("ROLE_EVPT")
     */
    public function content(): Response
    {
        return $this->redirectToRoute('admin_content_page_index');
    }

    /**
     * The conference area index page.
     *
     * @param ConferenceHandler The conference handler.
     * @return Response
     * @Route("/conference", name="conference")
     * @IsGranted("ROLE_ORGANISER")
     */
    public function conference(ConferenceHandler $conferenceHandler): Response
    {
        return $this->redirectToRoute('admin_conference_details_index');
    }

    /**
     * The journal area index page.
     *
     * @return Response
     * @Route("/journal", name="journal")
     * @IsGranted("ROLE_EDITOR")
     */
    public function journal(): Response
    {
        return $this->redirectToRoute('admin_journal_issue_index');
    }

    /**
     * The society area index page.
     *
     * @return Response
     * @Route("/society", name="society")
     * @IsGranted("ROLE_EVPT")
     */
    public function society(): Response
    {
        return $this->redirectToRoute('admin_society_candidate_index');
    }

    /**
     * The user area index page.
     *
     * @return Response
     * @Route("/user", name="user")
     * @IsGranted("ROLE_TECH")
     */
    public function user(): Response
    {
        return $this->redirectToRoute('admin_user_account_index');
    }
}
