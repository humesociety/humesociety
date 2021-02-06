<?php

namespace App\Controller\Journal;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The default controller for the journal-manager area.
 *
 * Access is restricted to those with ROLE_EDITOR. This is configured for any '/journal-manager' URL
 * in the config/packages/security.yaml, and also - you can't be too careful - with annotations here.
 *
 * @Route("/journal-manager", name="journal_")
 * @IsGranted("ROLE_EDITOR")
 */
class DefaultController extends AbstractController
{
    /**
     * The journal-manager index page; redirect.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('journal_issue_index');
    }
}
