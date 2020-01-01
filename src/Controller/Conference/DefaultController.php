<?php

namespace App\Controller\Conference;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The default controller for the conference-manager area.
 *
 * Access is restricted to those with ROLE_ORGANISER. This is configured for any '/conference-manager'
 * URL in the config/packages/security.yaml, and also - you can't be too careful - with annotations here.
 *
 * @Route("/conference-manager", name="conference_")
 * @IsGranted("ROLE_ORGANISER")
 */
class DefaultController extends AbstractController
{
    /**
     * The conference-manager index page; redirect.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('conference_details_index');
    }
}
