<?php

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin_")
 *
 * Access is restricted to those with ROLE_ADMIN. This is configured for any '/admin' URL in the
 * config/packages/security.yaml, and also - you can't be too careful - with annotations here.
 */
class DefaultController extends AbstractController
{
    /**
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
     * @Route("/content", name="content")
     * @IsGranted("ROLE_EVPT")
     */
    public function content(): Response
    {
        return $this->redirectToRoute('admin_content_page_index');
    }

    /**
     * @Route("/conference", name="conference")
     * @IsGranted("ROLE_ORGANISER")
     */
    public function conference(): Response
    {
        return $this->redirectToRoute('admin_conference_details_index');
    }

    /**
     * @Route("/journal", name="journal")
     * @IsGranted("ROLE_EDITOR")
     */
    public function journal(): Response
    {
        return $this->redirectToRoute('admin_journal_issue_index');
    }

    /**
     * @Route("/society", name="society")
     * @IsGranted("ROLE_EVPT")
     */
    public function society(): Response
    {
        return $this->redirectToRoute('admin_society_candidate_index');
    }

    /**
     * @Route("/user", name="user")
     * @IsGranted("ROLE_TECH")
     */
    public function user(): Response
    {
        return $this->redirectToRoute('admin_user_account_index');
    }
}
