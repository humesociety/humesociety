<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\EmailHandler;
use App\Entity\Email\EmailTemplate;
use App\Entity\Email\EmailTemplateType;
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
     * The email index page.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_email_view');
    }

    /**
     * The page for viewing email templates.
     *
     * @return Response
     * @Route("/view", name="view")
     */
    public function view(): Response
    {
        return $this->render('admin/conference/email/view.twig', [
            'area' => 'conference',
            'subarea' => 'email'
        ]);
    }

    /**
     * The page for editing an email template.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param EmailHandler The email handler.
     * @param string The email template type.
     * @return Response
     * @Route("/edit/{type}", name="edit", requirements={"type": "%conference_email_template_ids%"})
     */
    public function edit(
        Request $request,
        ConferenceHandler $conferenceHandler,
        EmailHandler $emailHandler,
        string $type
    ): Response {
        // look for the email template in the database
        $emailTemplate = $emailHandler->getEmailTemplateByType($type);

        // create a new one if it doesn't exist
        if (!$emailTemplate) {
            $emailTemplate = new EmailTemplate();
            $emailTemplate->setType($type)->setSender('conference');
        }

        // email template form
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailHandler->saveEmailTemplate($emailTemplate);
            $this->addFlash('notice', '"'.$emailTemplate.'" email template has been modified.');
            return $this->redirectToRoute('admin_conference_email_view');
        }

        // return the response
        return $this->render('admin/conference/email/edit.twig', [
            'area' => 'conference',
            'subarea' => 'email',
            'conference' => $conferenceHandler->getCurrentConference(),
            'emailTemplate' => $emailTemplate,
            'emailTemplateForm' => $form->createView(),
            'formName' => $form->getName()
        ]);
    }
}
