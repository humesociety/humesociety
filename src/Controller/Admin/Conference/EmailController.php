<?php

namespace App\Controller\Admin\Conference;

use App\Entity\EmailTemplate\EmailTemplateHandler;
use App\Entity\EmailTemplate\EmailTemplateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for sending out bulk conference-related emails.
 *
 * @Route("/admin/conference/email", name="admin_conference_email_")
 * @IsGranted("ROLE_ORGANISER")
 */
class EmailController extends AbstractController
{
    /**
     * Route for viewing all email templates.
     *
     * @param EmailTemplateHandler The email template handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(EmailTemplateHandler $emailTemplates): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'email',
            'conferenceEmailTemplates' => $emailTemplates->getConferenceEmailTemplates()
        ];

        // render and return the page
        return $this->render('admin/conference/email/view.twig', $twigs);
    }
}
