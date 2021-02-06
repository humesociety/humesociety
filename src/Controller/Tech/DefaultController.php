<?php

namespace App\Controller\Tech;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The default controller for the tech area.
 *
 * Access is restricted to those with ROLE_TECH. This is configured for any '/tech' URL in the
 * config/packages/security.yaml, and also - you can't be too careful - with annotations here.
 *
 * @Route("/tech", name="tech_")
 * @IsGranted("ROLE_TECH")
 */
class DefaultController extends AbstractController
{
    /**
     * The tech index page; redirect.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('tech_account_index');
    }
}
