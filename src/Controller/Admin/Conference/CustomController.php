<?php

namespace App\Controller\Admin\Conference;

use App\Entity\EmailTemplate\EmailTemplateHandler;
use App\Entity\EmailTemplate\EmailTemplateType;
use App\Entity\Text\TextHandler;
use App\Entity\Text\TextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference text variables and email templates.
 *
 * @Route("/admin/conference/custom", name="admin_conference_custom_")
 * @IsGranted("ROLE_ORGANISER")
 */
class CustomController extends AbstractController
{
    /**
     * Route for viewing conference text variables and email templates.
     *
     * @param EmailTemplateHandler The email template handler.
     * @param TextHandler The text handler.
     * @param string The initially visible tab.
     * @return Response
     * @Route("/{tab}", name="index")
     */
    public function index(EmailTemplateHandler $emailTemplates, TextHandler $texts, string $tab = 'text'): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'custom',
            'tab' => $tab,
            'texts' => $texts->getConferenceTexts(),
            'conferenceEmailTemplates' => $emailTemplates->getConferenceEmailTemplates()
        ];

        // render and return the page
        return $this->render('admin/conference/custom/view.twig', $twigs);
    }

    /**
     * Route for editing a conference text variable.
     *
     * @param Request Symfony's request object.
     * @param TextHandler The text handler.
     * @param string The text's label.
     * @return Response
     * @Route("/text/{label}", name="text", requirements={"label": "%conference_text_ids%"})
     */
    public function text(Request $request, TextHandler $texts, string $label): Response
    {
        // get the text variable
        $text = $texts->getTextByLabel($label);

        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'custom',
            'text' => $text,
        ];

        // create and handle the text edit form
        $textForm = $this->createForm(TextType::class, $text);
        $textForm->handleRequest($request);
        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $texts->saveText($text);
            $this->addFlash('notice', $text.' text has been updated.');
            return $this->redirectToRoute('admin_conference_custom_index');
        }

        // add additional twig variables
        $twigs['textForm'] = $textForm->createView();
        $twigs['formName'] = $textForm->getName(); // for the preview

        // render and return the page
        return $this->render('admin/conference/custom/text.twig', $twigs);
    }

    /**
     * Route for editing an email template.
     *
     * @param Request Symfony's request object.
     * @param EmailTemplateHandler The email template handler.
     * @param string The email template label.
     * @return Response
     * @Route("/email/{label}", name="email", requirements={"label": "%conference_email_template_ids%"})
     */
    public function email(Request $request, EmailTemplateHandler $emailTemplates, string $label): Response
    {
        // get the email template
        $emailTemplate = $emailTemplates->getEmailTemplateByLabel($label);

        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'custom',
            'emailTemplate' => $emailTemplate
        ];

        // create and handle the email template form
        $emailTemplateForm = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $emailTemplateForm->handleRequest($request);
        if ($emailTemplateForm->isSubmitted() && $emailTemplateForm->isValid()) {
            $emailTemplates->saveEmailTemplate($emailTemplate);
            $this->addFlash('notice', $emailTemplate.' email has been modified.');
            return $this->redirectToRoute('admin_conference_custom_index', ['tab' => 'email']);
        }

        // add additional twig variables
        $twigs['emailTemplateForm'] = $emailTemplateForm->createView();
        $twigs['formName'] = $emailTemplateForm->getName(); // for the preview

        // render and return the page
        return $this->render('admin/conference/custom/email.twig', $twigs);
    }
}
