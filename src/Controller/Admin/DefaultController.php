<?php

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The default controller for the admin area.
 *
 * Access is restricted to those with ROLE_ADMIN. This is configured for any '/admin' URL in the
 * config/packages/security.yaml, and also - you can't be too careful - with annotations here.
 *
 * @Route("/admin", name="admin_")
 * @IsGranted("ROLE_EVPT")
 */
class DefaultController extends AbstractController
{
    /**
     * The admin index page; redirect according to the user's permissions.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_page_index');
    }
}
