<?php

namespace App\Entity\Email;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The email handler, for managing email templates and sending emails.
 */
class EmailHandler
{
    /**
     * Swift Mailer.
     *
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The email template repository.
     *
     * @var EmailTemplateRepository
     */
    private $repository;

    /**
     * The conference handler.
     *
     * @var ConferenceHandler
     */
    private $conferenceHandler;

    /**
     * The user handler .
     *
     * @var UserHandler
     */
    private $userHandler;

    /**
     * The path to the project's root directory.
     *
     * @var string
     */
    private $projectDir;

    /**
     * Associative array of country codes and names.
     *
     * @var object
     */
    private $countries;

    /**
     * Constructor function.
     *
     * @param \Swift_Mailer Swift Mailer.
     * @param \Twig_Environment. Twig.
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param ConferenceHandler The conference handler.
     * @param UserHandler The user handler.
     * @param ParameterBagInterface Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $templating,
        EntityManagerInterface $manager,
        ConferenceHandler $conferenceHandler,
        UserHandler $userHandler,
        ParameterBagInterface $params
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->manager = $manager;
        $this->repository = $manager->getRepository(EmailTemplate::class);
        $this->conferenceHandler = $conferenceHandler;
        $this->userHandler = $userHandler;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->countries = $params->get('countries');
    }

    /**
     * Get email templates from the database.
     *
     * @return EmailTemplate[]
     */
    public function getEmailTemplates(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get a specific email template.
     *
     * @param string The type of the template to get.
     * @return EmailTemplate|null
     */
    public function getEmailTemplateByType(string $type): ?EmailTemplate
    {
        return $this->repository->findOneByType($type);
    }

    /**
     * Save/update an email template.
     *
     * @param EmailTemplate The email template to save/update.
     * @return void
     */
    public function saveEmailTemplate(EmailTemplate $emailTemplate)
    {
        $this->manager->persist($emailTemplate);
        $this->manager->flush();
    }

    /**
     * Replace recipient-related variables in email text.
     *
     * @param string The email text.
     * @param User|Reviewer The recipient of the email.
     * @return string
     */
    private function prepareRecipientContent(string $text, $recipient): string
    {
        $text = preg_replace('/{{ ?email ?}}/', $recipient->getEmail(), $text);
        $text = preg_replace('/{{ ?firstname ?}}/', $recipient->getFirstname(), $text);
        $text = preg_replace('/{{ ?lastname ?}}/', $recipient->getLastname(), $text);
        return $text;
    }

    /**
     * Replace submission-related variables in email text.
     *
     * @param string The email text.
     * @param Submission The submission.
     * @return string
     */
    private function prepareSubmissionContent(string $text, Submission $submission): string
    {
        $text = preg_replace('/{{ ?title ?}}/', $submission->getTitle(), $text);
        $text = preg_replace('/{{ ?abstract ?}}/', $submission->getAbstract(), $text);
        $conference = $submission->getConference();
        $text = preg_replace('/{{ ?ordinal ?}}/', $conference->getOrdinal(), $text);
        $text = preg_replace('/{{ ?town ?}}/', $conference->getTown(), $text);
        $text = preg_replace('/{{ ?country ?}}/', $this->countries[$conference->getCountry()], $text);
        return $text;
    }

    /**
     * Create a swift message email.
     *
     * @param User|Reviewer The recipient of the email.
     * @param string The sender of the email.
     * @param string The subject of the email.
     * @param string The content of the email.
     * @param string|null Path to any attachment.
     * @return \Swift_Message
     */
    private function createEmail(
        User $recipient,
        string $sender,
        string $subject,
        string $content,
        ?string $pathToAttachment = null
    ): \Swift_Message {
        // create the email
        $email = new \Swift_Message($subject);

        // create the body of the email
        $body = $this->templating->render('email/base.twig', [
            'image' => $email->embed(\Swift_Image::fromPath($this->projectDir.'/public/logo.jpg')),
            'content' => $content
        ]);

        // set the email fields
        $email->setFrom($this->userHandler->getOfficialEmail($sender));
        $email->setBody($body, 'text/html');
        $email->setTo([
            $recipient->getEmail() => $recipient->getFirstname().' '.$recipient->getLastname()
        ]);
        if ($pathToAttachment) {
            $email->attach(\Swift_Attachment::fromPath($pathToAttachment));
        }

        // return the email
        return $email;
    }

    /**
     * Send an email to all members.
     *
     * @param bool Whether to include current members.
     * @param bool Whether to include lapsed members.
     * @param bool Whether to include members who have declined to recieve general emails.
     * @param string The sender of the email.
     * @param string The subject of the email.
     * @param string The content of the email.
     * @param string|null The path to any attachment.
     * @return object
     */
    public function sendMembershipEmail(
        bool $current,
        bool $lapsed,
        bool $declining,
        string $sender,
        string $subject,
        string $content,
        ?string $pathToAttachment = null
    ): array {
        // get the recipients of the email
        $recipientUsers = [];
        if ($current) {
            foreach ($this->userHandler->getMembersInGoodStanding() as $user) {
                if ($declining || $user->getReceiveEmail()) {
                    $recipientUsers[] = $user;
                }
            }
        }
        if ($lapsed) {
            foreach ($this->userHandler->getMembersInArrears() as $user) {
                if ($declining || $user->getReceiveEmail()) {
                    $recipientUsers[] = $user;
                }
            }
        }

        // send the email to each recipient, keeping track of the results
        $emailsSent = 0;
        $goodRecipients = [];
        $badRecipients = [];
        foreach ($recipientUsers as $user) {
            $thisSubject = $this->prepareRecipientContent($subject, $user);
            $thisContent = $this->prepareRecipientContent($content, $user);
            $email = $this->createEmail($user, $sender, $thisSubject, $thisContent, $pathToAttachment);
            $sent = $this->mailer->send($email);
            if ($sent) {
                $emailsSent += 1;
                $goodRecipients[] = $user->getEmail();
            } else {
                $badRecipients[] = $user->getEmail();
            }
        }

        // return the results
        return [
            'emailsSent' => $emailsSent,
            'goodRecipients' => $goodRecipients,
            'badRecipients' => $badRecipients
        ];
    }

    /**
     * Send a password reset email.
     *
     * @param User The recipient of the email.
     * @return void
     */
    public function sendResetPasswordEmail(User $user)
    {
        $email = new \Swift_Message('Hume Society Password Reset');
        $email->setFrom($this->userHandler->getOfficialEmail('web'));
        $email->setTo([$user->getEmail() => $user->getFirstname().' '.$user->getLastname()]);
        $email->setBody($this->templating->render('email/reset-password.twig', [
            'image' => $email->embed(\Swift_Image::fromPath($this->projectDir.'/public/logo.jpg')),
            'user' => $user
        ]), 'text/html');
        $this->mailer->send($email);
    }

    /**
     * Send society email from template.
     *
     * @param User The recipient of the email.
     * @param string The template to use.
     * @return void
     */
    public function sendSocietyEmail(User $user, string $template)
    {
        $emailTemplate = $this->getEmailTemplateByType($template);
        if ($emailTemplate) {
            $sender = $emailTemplate->getSender();
            $subject = $this->prepareRecipientContent($emailTemplate->getSubject(), $user);
            $content = $this->prepareRecipientContent($emailTemplate->getContent(), $user);
            $email = $this->createEmail($user, $sender, $subject, $content);
            $this->mailer->send($email);
        }
    }

    /**
     * Send conference email from template.
     *
     * @param User|Reviewer The recipient of the email.
     * @param Submission The submission concerned.
     * @param string The template to use.
     * @return void
     */
    public function sendConferenceEmail($recipient, Submission $submission, string $template)
    {
        $emailTemplate = $this->getEmailTemplateByType($template);
        if ($emailTemplate) {
            $sender = $emailTemplate->getSender();
            $subject = $this->prepareRecipientContent($emailTemplate->getSubject(), $recipient);
            $subject = $this->prepareSubmissionContent($subject, $submission);
            $content = $this->prepareRecipientContent($emailTemplate->getContent(), $recipient);
            $content = $this->prepareSubmissionContent($content, $submission);
            $email = $this->createEmail($recipient, $sender, $subject, $content);
            $this->mailer->send($email);
        }
    }

    /**
     * Send submission notification email to conference organisers.
     *
     * @param Submission The submission.
     * @return void
     */
    public function sendSubmissionNotification(Submission $submission)
    {
        $conference = $this->conferenceHandler->getCurrentConference();
        if ($conference) {
            // if there's no current conference, this function should never be called, since no
            // submissions will be possible
            $email = new \Swift_Message('Submission to the '.$conference);
            $email->setFrom($this->userHandler->getOfficialEmail('web'));
            $email->setTo($this->userHandler->getOfficialEmail('conference'));
            $email->setBody($this->templating->render('email/submission-received.twig', [
                'image' => $email->embed(\Swift_Image::fromPath($this->projectDir.'/public/logo.jpg')),
                'conference' => $conference,
                'submission' => $submission
            ]), 'text/html');
            $this->mailer->send($email);
        }
    }
}
