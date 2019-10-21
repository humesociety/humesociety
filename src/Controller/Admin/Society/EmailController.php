<?php

namespace App\Controller\Admin\Society;

use App\Entity\EmailTemplate\EmailTemplateType;
use App\Entity\EmailTemplate\EmailTemplateHandler;
use App\Entity\Email\Email;
use App\Entity\Email\SocietyEmailHandler;
use App\Entity\Email\EmailTypeMembership;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for emailing the society members, and editing society email templates.
 *
 * @Route("/admin/society/email", name="admin_society_email_")
 * @IsGranted("ROLE_EVPT")
 */
class EmailController extends AbstractController
{
    /**
     * Route for viewing all society email options.
     *
     * @param EmailTemplateHandler The email template handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(EmailTemplateHandler $emailTemplates): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'email',
            'emailTemplates' => $emailTemplates->getEmailTemplates('society')
        ];

        // render and return the page
        return $this->render('admin/society/email/view.twig', $twigs);
    }

    /**
     * Route for sending a membership email.
     *
     * @param Request Symfony's request object.
     * @param SocietyEmailHandler The society email handler.
     * @return Response
     * @Route("/send", name="send")
     */
    public function send(Request $request, SocietyEmailHandler $societyEmails): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'email'
        ];

        // create and handle the email form
        $email = new Email();
        $emailForm = $this->createForm(EmailTypeMembership::class, $email);
        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $current = $emailForm->get('current')->getData();
            $lapsed = $emailForm->get('lapsed')->getData();
            if ($current || $lapsed) {
                $declining = $emailForm->get('declining')->getData();
                $results = $societyEmails->sendMembershipEmail($current, $lapsed, $declining, $email);
                $this->addFlash('notice', 'Your email will be sent to '.$results['emailsSent'].' recipients.');
                if (sizeof($results['badRecipients']) > 0) {
                    $error = 'Your email could not be sent to the following addresses: ';
                    $error .= join(', ', $results['badRecipients']);
                    $this->addFlash('error', $error);
                }
            } else {
                $form->get('current')->addError(new FormError('You must select some recipients.'));
            }
        }

        // add additional twig variables
        $twigs['emailForm'] = $emailForm->createView();
        $twigs['formName'] = $emailForm->getName(); // for the preview

        // render and return the page
        return $this->render('admin/society/email/send.twig', $twigs);
    }

    /**
     * Route for editing a society email template.
     *
     * @param Request Symfony's request object.
     * @param EmailTemplateHandler The email template handler.
     * @param string The email template's label.
     * @return Response
     * @Route("/edit/{label}", name="edit", requirements={"label": "%society_email_template_ids%"})
     */
    public function edit(Request $request, EmailTemplateHandler $emailTemplates, string $label): Response
    {
        // get the email template
        $emailTemplate = $emailTemplates->getEmailTemplateByLabel($label);

        // initialise the twig variables
        $twigs = [
          'area' => 'society',
          'subarea' => 'email',
          'emailTemplate' => $emailTemplate
        ];

        // create and handle the email template form
        $emailTemplateForm = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $emailTemplateForm->handleRequest($request);
        if ($emailTemplateForm->isSubmitted() && $emailTemplateForm->isValid()) {
            $emailTemplates->saveEmailTemplate($emailTemplate);
            $this->addFlash('notice', $emailTemplate.' email has been modified.');
            return $this->redirectToRoute('admin_society_email_index');
        }

        // add additional twig variables
        $twigs['emailTemplateForm'] = $emailTemplateForm->createView();
        $twigs['formName'] = $emailTemplateForm->getName(); // for the preview

        // render and return the page
        return $this->render('admin/society/email/edit.twig', $twigs);
    }
}
