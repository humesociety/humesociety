<?php

namespace App\Controller\Admin\Society;

use App\Entity\EmailTemplate\EmailTemplateType;
use App\Entity\Email\EmailType;
use App\Service\Emailer;
use App\Service\EmailTemplateManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/society/email", name="admin_society_email_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for emailing the society members, and editing and setting up automatic
 * emails.
 */
class EmailController extends AbstractController
{
    /**
     * Route for viewing all society email options.
     *
     * @Route("/", name="index")
     */
    public function index(EmailTemplateManager $emailTemplates): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'email',
            'emailTemplates' => $emailTemplates->getSocietyEmailTemplates()
        ];

        // render and return the page
        return $this->render('admin/society/email/view.twig', $twigs);
    }

    /**
     * Route for sending a membership email.
     *
     * @Route("/send", name="send")
     */
    public function send(Request $request, Emailer $emailer): Response
    {
        // initialise the twig variables
        $twig = [
            'area' => 'society',
            'subarea' => 'email',
            'emailForm' => $form->createView(),
            'formName' => $form->getName()
        ];

        // create and handle the email form
        $email = new Email();
        $emailForm = $this->createForm(SocietyEmailType::class, $email);
        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $current = $form->get('current')->getData();
            $lapsed = $form->get('lapsed')->getData();
            if ($current || $lapsed) {
                $declining = $form->get('declining')->getData();
                $results = $emailer->sendMembershipEmail($current, $lapsed, $declining, $email);
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

        // render and return the page
        return $this->render('admin/society/email/send.twig', $twigs);
    }

    /**
     * Route for editing a society email template.
     *
     * @Route("/edit/{label}", name="edit", requirements={"label": "%society_email_template_ids%"})
     */
    public function edit(Request $request, EmailTemplateManager $emailTemplates, string $label): Response
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
        $twigs['emailTemplateForm'] = $emailTemplateForm->createView();
        $twigs['formName'] = $emailTemplateForm->getName(); // for the preview
        $emailTemplateForm->handleRequest($request);
        if ($emailTemplateForm->isSubmitted() && $emailTemplateForm->isValid()) {
            $emailTemplates->saveEmailTemplate($emailTemplate);
            $this->addFlash('notice', $emailTemplate.' email has been modified.');
            return $this->redirectToRoute('admin_society_email_index');
        }

        // render and return the page
        return $this->render('admin/society/email/edit.twig', $twigs);
    }
}
