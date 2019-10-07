<?php

namespace App\Controller\Admin\Conference;

use App\Entity\EmailTemplate\EmailTemplateType;
use App\Service\EmailTemplateManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference email templates.
 *
 * @Route("/admin/conference/email", name="admin_conference_email_")
 * @IsGranted("ROLE_ORGANISER")
 */
class EmailController extends AbstractController
{
    /**
     * Route for viewing all email templates.
     *
     * @param EmailTemplateManager The email template manager.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(EmailTemplateManager $emailTemplates): Response
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

    /**
     * Route for editing an email template.
     *
     * @param Request Symfony's request object.
     * @param EmailTemplateManager The email template manager.
     * @param string The email template label.
     * @return Response
     * @Route("/edit/{label}", name="edit", requirements={"label": "%conference_email_template_ids%"})
     */
    public function edit(Request $request, EmailTemplateManager $emailTemplates, string $label): Response
    {
        // get the email template
        $emailTemplate = $emailTemplates->getEmailTemplateByLabel($label);

        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'email',
            'emailTemplate' => $emailTemplate
        ];

        // create and handle the email template form
        $emailTemplateForm = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $twigs['emailTemplateForm'] = $emailTemplateForm->createView();
        $twigs['formName'] = $emailTemplateForm->getName(); // for the preview
        $emailTemplateForm->handleRequest($request);
        if ($emailTemplateForm->isSubmitted() && $emailTemplateForm->isValid()) {
            $emailTemplates->saveEmailTemplate($emailTemplate);
            $this->addFlash('notice', $emailTemplate.' email has been modified.');
            return $this->redirectToRoute('admin_conference_email_index');
        }

        // render and return the page
        return $this->render('admin/conference/email/edit.twig', $twigs);
    }
}
