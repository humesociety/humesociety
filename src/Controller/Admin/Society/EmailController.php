<?php

namespace App\Controller\Admin\Society;

use App\Entity\Email\EmailHandler;
use App\Entity\Email\EmailTemplate;
use App\Entity\Email\EmailTemplateType;
use App\Entity\Email\EmailType;
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
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_society_email_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(Request $request, EmailHandler $emailHandler): Response
    {
        return $this->render('admin/society/email/view.twig', [
            'area' => 'society',
            'subarea' => 'email',
            'emailTemplates' => $emailHandler->getEmailTemplates()
        ]);
    }

    /**
     * @Route("/send", name="send")
     */
    public function send(Request $request, EmailHandler $emailHandler): Response
    {
        $form = $this->createForm(EmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $current = $form->get('current')->getData();
            $lapsed = $form->get('lapsed')->getData();

            if ($current || $lapsed) {
                $declining = $form->get('declining')->getData();
                $sender = $form->get('sender')->getData();
                $subject = $form->get('subject')->getData();
                $body = $form->get('content')->getData();

                $pathToAttachment = null;
                $file = $form->get('attachment')->getData();
                if ($file) {
                    $path = $this->getParameter('uploads_directory').'attachments/';
                    $filename = $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $pathToAttachment = $path.$filename;
                }

                $results = $emailHandler->sendMembershipEmail($current, $lapsed, $declining, $sender, $subject, $body, $pathToAttachment);
                $notice = 'Your email will be sent to '.$results['emailsSent'].' recipients.';
                $this->addFlash('notice', $notice);
                if (sizeof($results['badRecipients']) > 0) {
                    $error = 'Your email could not be sent to the following addresses: ';
                    $error .= join(', ', $results['badRecipients']);
                    $this->addFlash('error', $error);
                }
            } else {
                $form->get('current')->addError(new FormError('You must select some recipients.'));
            }
        }

        return $this->render('admin/society/email/send.twig', [
            'area' => 'society',
            'subarea' => 'email',
            'emailForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{type}", name="edit", requirements={"type": "%society_email_template_ids%"})
     */
    public function edit(
        string $type,
        Request $request,
        EmailHandler $emailHandler
    ): Response {
        $emailTemplate = $emailHandler->getEmailTemplateByType($type);

        if (!$emailTemplate) {
            $emailTemplate = new EmailTemplate();
            $emailTemplate->setType($type);
        }

        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailHandler->saveEmailTemplate($emailTemplate);
            $templateName = $this->getParameter('emails')[$emailTemplate->__toString()];
            $this->addFlash('notice', '"'.$emailTemplate.'" email template has been modified.');
            return $this->redirectToRoute('admin_society_email_view');
        }

        return $this->render('admin/society/email/edit.twig', [
            'area' => 'society',
            'subarea' => 'email',
            'emailTemplate' => $emailTemplate,
            'emailTemplateForm' => $form->createView()
        ]);
    }
}
