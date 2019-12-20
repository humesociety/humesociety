<?php

namespace App\Entity\Email;

use App\Entity\EmailTemplate\EmailTemplateHandler;
use App\Entity\User\UserHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The email handler contains core methods for sending emails. It is used by the various other (more
 * specific) email handlers.
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
     * Twig.
     *
     * @var \Twig\Environment
     */
    private $templating;

    /**
     * The path to the project's root directory.
     *
     * @var string
     */
    private $projectDir;

    /**
     * The email template handler.
     *
     * @var EmailTemplateHandler
     */
    private $emailTemplates;

    /**
     * The user handler .
     *
     * @var UserHandler
     */
    private $users;

    /**
     * Constructor function.
     *
     * @param \Swift_Mailer $mailer Swift Mailer.
     * @param \Twig\Environment $templating Twig.
     * @param ParameterBagInterface $params Symfony's parameter bag interface.
     * @param EmailTemplateHandler $emailTemplates The email template handler.
     * @param UserHandler $users The user handler.
     * @return void
     */
    public function __construct(
        \Swift_Mailer $mailer,
        \Twig\Environment $templating,
        ParameterBagInterface $params,
        EmailTemplateHandler $emailTemplates,
        UserHandler $users
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->emailTemplates = $emailTemplates;
        $this->users = $users;
    }

    /**
     * Get an official society email address (address => name).
     *
     * @param string $sender The type of email to get.
     * @return array
     */
    public function getOfficialEmail(string $sender): array
    {
        switch ($sender) {
            case 'vicepresident':
                $evpt = $this->users->getVicePresident();
                $name = $evpt ? $evpt->getFullname() : 'Executive Vice-President Treasurer';
                return ['vicepresident@humesociety.org' => $name];

            case 'conference':
                $organisers = $this->users->getConferenceOrganisers();
                $name = 'Conference Organisers';
                if (sizeof($organisers) > 0) {
                    $name = implode(', ', array_map(function ($organiser) {
                        return $organiser->getFullname();
                    }, $organisers));
                }
                return ['conference@humesociety.org' => $name];

            case 'web': // fallthrough
            default: // also make this the default, to ensure this function always returns something
                $tech = $this->users->getTechnicalDirector();
                $name = $tech ? $tech->getFullname() : 'Technical Director';
                return ['web@humesociety.org' => $name];
        }
    }

    /**
     * Send an email.
     *
     * @param Email $email The email to send.
     * @param string|null $pathToAttachment The path to an attachment.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return int
     */
    public function sendEmail(Email $email, ?string $pathToAttachment = null)
    {
        // create the email
        $message = new \Swift_Message($email->getSubject());
        $message->setFrom($this->getOfficialEmail($email->getSender()));
        $message->setTo([$email->getRecipient()->getEmail() => $email->getRecipient()->getFullname()]);

        // render and set the content of the email
        $email->addTwig('image', $message->embed(\Swift_Image::fromPath("{$this->projectDir}/public/logo.jpg")));
        $content = $this->templating->render("email/{$email->getTemplate()}.twig", $email->getTwigs());
        $message->setBody($content, 'text/html');

        // maybe attach a file
        if ($pathToAttachment) {
            $message->attach(\Swift_Attachment::fromPath($pathToAttachment));
        }

        // send the email
        return $this->mailer->send($message);
    }

    /**
     * Create an email from a template saved in the database.
     *
     * @param string $label The label of the template.
     * @return Email|null
     */
    public function emailFromTemplate(string $label): ?Email
    {
        $template = $this->emailTemplates->getEmailTemplateByLabel($label);
        if ($template->getContent()) {
            $email = new Email();
            $email->setSender($template->getSender())
                ->setSubject($template->getSubject())
                ->setContent($template->getContent());
            return $email;
        }
        return null;
    }
}
