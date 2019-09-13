<?php

namespace App\Entity\Email;

use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailHandler
{
    private $mailer;
    private $manager;
    private $repository;
    private $userHandler;
    private $logo;

    public function __construct(
        EntityManagerInterface $manager,
        \Swift_Mailer $mailer,
        ParameterBagInterface $params,
        UserHandler $userHandler
    ) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(EmailTemplate::class);
        $this->mailer = $mailer;
        $this->userHandler = $userHandler;
        $this->projectDir = $params->get('kernel.project_dir');
    }

    // gett email template(s) from database
    public function getEmailTemplates(): array
    {
        return $this->repository->findAll();
    }

    public function getEmailTemplateByType(string $type): ?EmailTemplate
    {
        return $this->repository->findOneByType($type);
    }

    // save email template to database
    public function saveEmailTemplate(EmailTemplate $emailTemplate)
    {
        $this->manager->persist($emailTemplate);
        $this->manager->flush();
    }

    // create a swift message email
    private function createEmail(
        User $user,
        array $sender,
        string $subject,
        string $body,
        ?string $pathToAttachment = null
    ): \Swift_Message {
        $email = new \Swift_Message($subject);
        $image = $email->embed(\Swift_Image::fromPath($this->projectDir.'/public/logo.jpg'));

        $body = preg_replace('/{{ ?username ?}}/', $user->getUsername(), $body);
        $body = preg_replace('/{{ ?email ?}}/', $user->getEmail(), $body);
        $body = preg_replace('/{{ ?firstname ?}}/', $user->getFirstname(), $body);
        $body = preg_replace('/{{ ?lastname ?}}/', $user->getLastname(), $body);
        $body = '<div style="font-size:16px;font-family:-apple-system,system-ui,BlinkMacSystemFont,Roboto,Oxygen,Ubuntu,Cantarell,Helvetica,Arial,sans-serif;line-height:1.5;color:#282828;max-width:600px;margin:1em auto;"><div><img src="'.$image.'" style="height:auto;max-width:100%;" alt="The Hume Society"></div><div style="padding:1em;">'.$body.'</div></div>';

        $email->setFrom($sender);
        $email->setBody($body, 'text/html');
        $email->setTo([$user->getEmail() => $user->getFirstname().' '.$user->getLastname()]);
        if ($pathToAttachment) {
            $email->attach(\Swift_Attachment::fromPath($pathToAttachment));
        }

        return $email;
    }

    // emailing functions
    public function sendMembershipEmail(
        bool $current,
        bool $lapsed,
        bool $declining,
        string $subject,
        string $body,
        ?string $pathToAttachment = null
    ): array {
        $sender = ['vicepresident@humesociety.org' => 'Emily Kelahan'];
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

        $emailsSent = 0;
        $goodRecipients = [];
        $badRecipients = [];
        foreach ($recipientUsers as $user) {
            $email = $this->createEmail($user, $sender, $subject, $body, $pathToAttachment);
            $sent = $this->mailer->send($email);
            // $sent = $this->sendWithPhpMail($email, $user);
            if ($sent) {
                $emailsSent += 1;
                $goodRecipients[] = $user->getEmail();
            } else {
                $badRecipients[] = $user->getEmail();
            }
        }

        return [
            'emailsSent' => $emailsSent,
            'goodRecipients' => $goodRecipients,
            'badRecipients' => $badRecipients
        ];
    }

    public function sendResetPasswordEmail(User $user)
    {
        $sender = ['web@humesociety.org' => 'Amyas Merivale'];
        $subject = 'Hume Society Password Reset';
        $link = 'https://www.humesociety.org/reset/'.$user->getUsername().'/'.$user->getPasswordResetSecret();
        $body = '<p>Dear {{ firstname }} {{ lastname }},</p>';
        $body .= '<p>We have received a request to send you your login credentials for the Hume Society web site. Your username is &ldquo;{{ username }}&rdquo;. If you wish to reset your password, please click on the button below.</p>';
        $body .= '<div style="text-align:center;margin:1em 0;"><a href="'.$link.'" style="background:#212f4b;color:#fff;text-decoration:none;padding:.5em 1em;display:inline-block;">Reset Password</a></div>';
        $body .= '<p>If you did not request this information, you can safely ignore this email. No one else will see the link to reset your password, and it will only remain active for 24 hours.</p>';
        $body .= '<p>Thank you,</p>';
        $body .= '<p>Amyas Merivale<br>Technical Director, The Hume Society</p>';

        $email = $this->createEmail($user, $sender, $subject, $body);
        $this->mailer->send($email);
    }

    public function sendNewMemberEmail(User $user)
    {
        $emailTemplate = $this->getEmailTemplateByType('welcome');
        $sender = [$emailTemplate->getSenderEmailAddress() => $emailTemplate->getSenderName()];
        $subject = $emailTemplate->getSubject();
        $body = $emailTemplate->getContent();

        $email = $this->createEmail($user, $sender, $subject, $body);
        $this->mailer->send($email);
    }

    public function sendDuesReminderEmail(User $user)
    {
        $emailTemplate = $this->getEmailTemplateByType('dues-reminder');
        $sender = [$emailTemplate->getSenderEmailAddress() => $emailTemplate->getSenderName()];
        $subject = $emailTemplate->getSubject();
        $body = $emailTemplate->getContent();

        $email = $this->createEmail($user, $sender, $subject, $body);
        $this->mailer->send($email);
    }
}
