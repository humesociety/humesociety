<?php

namespace App\Entity\Email;

use App\Entity\Conference\Conference;
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
     * Swift Mailer (dependency injection).
     *
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * The Doctrine entity manager (dependency injection).
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The email template repository (dependency injection).
     *
     * @var EmailTemplateRepository
     */
    private $repository;

    /**
     * The user handler (dependency injection).
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
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param UserHandler The user handler.
     * @param ParameterBagInterface Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EntityManagerInterface $manager,
        UserHandler $userHandler,
        ParameterBagInterface $params
    ) {
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->repository = $manager->getRepository(EmailTemplate::class);
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
     * Replace user-related variables in email text.
     *
     * @param string The email text.
     * @param User The user.
     * @return string
     */
    private function prepareUserContent(string $text, User $user): string
    {
        $text = preg_replace('/{{ ?username ?}}/', $user->getUsername(), $text);
        $text = preg_replace('/{{ ?email ?}}/', $user->getEmail(), $text);
        $text = preg_replace('/{{ ?firstname ?}}/', $user->getFirstname(), $text);
        $text = preg_replace('/{{ ?lastname ?}}/', $user->getLastname(), $text);
        return $text;
    }

    /**
     * Replace conference-related variables in email text.
     *
     * @param string The email text.
     * @param Conference The conference.
     * @return string
     */
    private function prepareConferenceContent(string $text, Conference $conference): string
    {
        $text = preg_replace('/{{ ?ordinal ?}}/', $conference->getOrdinal(), $text);
        $text = preg_replace('/{{ ?town ?}}/', $conference->getTown(), $text);
        $text = preg_replace('/{{ ?country ?}}/', $this->countries[$conference->getCountry()], $text);
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
        $text = $this->prepareUserContent($text, $submission->getUser());
        $text = $this->prepareConferenceContent($text, $submission->getConference());
        $text = preg_replace('/{{ ?title ?}}/', $submission->getTitle(), $text);
        return $text;
    }

    /**
     * Create a swift message email.
     *
     * @param User The recipient of the email.
     * @param string The sender of the email.
     * @param string The subject of the email.
     * @param string The content of the email.
     * @param string|null Path to any attachment.
     * @return \Swift_Message
     */
    private function createEmail(
        User $user,
        string $sender,
        string $subject,
        string $content,
        ?string $pathToAttachment = null
    ): \Swift_Message {
        // create the email and an embedded logo image
        $email = new \Swift_Message($subject);
        $image = $email->embed(\Swift_Image::fromPath($this->projectDir.'/public/logo.jpg'));

        // create the body of the email
        $body = '<div style="font-size:16px;font-family:-apple-system,system-ui,BlinkMacSystemFont,'
              . 'Roboto,Oxygen,Ubuntu,Cantarell,Helvetica,Arial,sans-serif;line-height:1.5;'
              . 'color:#282828;max-width:600px;margin:1em auto;"><div><img src="'. $image
              . '" style="height:auto;max-width:100%;" alt="The Hume Society"></div><div style="padding:1em;">'
              . $content
              . '</div></div>';

        // set the other email fields
        $email->setFrom($this->userHandler->getOfficialEmail($sender));
        $email->setBody($body, 'text/html');
        $email->setTo([$user->getEmail() => $user->getFirstname().' '.$user->getLastname()]);
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
            $content = $this->prepareUserContent($content, $user);
            $email = $this->createEmail($user, $sender, $subject, $content, $pathToAttachment);
            $sent = $this->mailer->send($email);
            // $sent = $this->sendWithPhpMail($email, $user);
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
        $sender = 'web';
        $subject = 'Hume Society Password Reset';
        $link = 'https://www.humesociety.org/reset/'
              . $user->getUsername().'/'
              . $user->getPasswordResetSecret();
        $content = '<p>Dear {{ firstname }} {{ lastname }},</p>'
              . '<p>We have received a request to send you your login credentials for the Hume '
              . 'Society web site. Your username is &ldquo;{{ username }}&rdquo;. If you wish to '
              . 'reset your password, please click on the button below.</p>'
              . '<div style="text-align:center;margin:1em 0;"><a href="'.$link
              . '" style="background:#212f4b;color:#fff;text-decoration:none;padding:.5em 1em;'
              . 'display:inline-block;">Reset Password</a></div>'
              . '<p>If you did not request this information, you can safely ignore this email. No '
              . 'one else will see the link to reset your password, and it will only remain active '
              . 'for 24 hours.</p>'
              . '<p>Thank you,</p>'
              . '<p>Amyas Merivale<br>Technical Director, The Hume Society</p>';
        $content = $this->prepareUserContent($content, $user);

        $email = $this->createEmail($user, $sender, $subject, $content);
        $this->mailer->send($email);
    }

    /**
     * Send new member welcome email.
     *
     * @param User The recipient of the email.
     * @return void
     */
    public function sendNewMemberEmail(User $user)
    {
        $emailTemplate = $this->getEmailTemplateByType('welcome');
        if ($emailTemplate) {
            $sender = $emailTemplate->getSender();
            $subject = $emailTemplate->getSubject();
            $content = $this->prepareUserContent($emailTemplate->getContent(), $user);
            $email = $this->createEmail($user, $sender, $subject, $content);
            $this->mailer->send($email);
        }
    }

    /**
     * Send dues reminder email.
     *
     * @param User The recipient of the email.
     * @return void
     */
    public function sendDuesReminderEmail(User $user)
    {
        $emailTemplate = $this->getEmailTemplateByType('reminder');
        if ($emailTemplate) {
            $sender = $emailTemplate->getSender();
            $subject = $emailTemplate->getSubject();
            $content = $this->prepareUserContent($emailTemplate->getContent(), $user);
            $email = $this->createEmail($user, $sender, $subject, $content);
            $this->mailer->send($email);
        }
    }

    /**
     * Send submission acknowledgement email.
     *
     * @param Submission The submission to acknowledge.
     * @return void
     */
    public function sendSubmissionAcknowledgementEmail(Submission $submission)
    {
        $emailTemplate = $this->getEmailTemplateByType('submission');
        if ($emailTemplate) {
            $sender = $emailTemplate->getSender();
            $subject = $emailTemplate->getSubject();
            $content = $this->prepareSubmissionContent($emailTemplate->getContent(), $submission);
            $email = $this->createEmail($user, $sender, $subject, $content);
            $this->mailer->send($email);
        }
    }
}
