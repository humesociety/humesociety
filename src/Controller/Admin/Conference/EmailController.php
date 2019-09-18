<?php

namespace App\Controller\Admin\Conference;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/conference/email", name="admin_conference_email_")
 * @IsGranted("ROLE_ORGANISER")
 *
 * Controller for managing conference email templates.
 */
class EmailController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_email_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(): Response
    {
        return $this->render('admin/conference/email/view.twig', [
            'area' => 'conference',
            'subarea' => 'email'
        ]);
    }
}
