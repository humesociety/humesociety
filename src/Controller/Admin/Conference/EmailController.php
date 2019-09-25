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

    /**
     * @Route("/edit/{type}", name="edit", requirements={"type": "submission|accept|reject|review|thanks"})
     */
    public function edit(
        string $type,
        Request $request,
        ConferenceHandler $conferenceHandler,
        EmailHandler $emailHandler
    ): Response {
        $emailTemplate = $emailHandler->getEmailTemplateByType($type);

        if (!$emailTemplate) {
            $emailTemplate = new EmailTemplate();
            $emailTemplate->setType($type)->setSender('conference');
        }

        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailHandler->saveEmailTemplate($emailTemplate);
            $this->addFlash('notice', '"'.$emailTemplate.'" email template has been modified.');
            return $this->redirectToRoute('admin_conference_email_view');
        }

        return $this->render('admin/conference/email/edit.twig', [
            'area' => 'conference',
            'subarea' => 'email',
            'conference' => $conferenceHandler->getCurrentConference(),
            'emailTemplate' => $emailTemplate,
            'emailTemplateForm' => $form->createView()
        ]);
    }
}
