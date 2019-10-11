<?php

namespace App\Entity\Email;

use App\Entity\EmailTemplate\EmailTemplate;
use App\Entity\EmailTemplate\EmailTemplateHandler;
use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The email handler contains methods for sending emails.
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
     * @var \Twig_Environment
     */
    private $templating;

    /**
     * The path to the project's root directory.
     *
     * @var string
     */
    private $projectDir;

    /**
     * The path to the attachments directory.
     *
     * @var string
     */
    private $attachmentsDir;

    /**
     * Associative array of country codes and names.
     *
     * @var object
     */
    private $countries;

    /**
     * The conference handler.
     *
     * @var ConferenceHandler
     */
    private $conferences;

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
     * @param \Swift_Mailer Swift Mailer.
     * @param \Twig_Environment. Twig.
     * @param ParameterBagInterface Symfony's parameter bag interface.
     * @param ConferenceHandler The conference handler.
     * @param EmailTemplateHandler The email template handler.
     * @param UserHandler The user handler.
     * @return void
     */
    public function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $templating,
        ParameterBagInterface $params,
        ConferenceHandler $conferences,
        EmailTemplateHandler $emailTemplates,
        UserHandler $users
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->attachmentsDir = "{$params->get('uploads_directory')}attachments/";
        $this->countries = $params->get('countries');
        $this->conferences = $conferences;
        $this->emailTemplates = $emailTemplates;
        $this->users = $users;
    }

    /**
     * Get an official society email address (address => name).
     *
     * @param string The type of email to get.
     * @return object
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
     * @param Email The email to send.
     * @param string|null The path to an attachment.
     * @return void
     */
    private function sendEmail(Email $email, ?string $pathToAttachment = null)
    {
        // create the email
        $message = new \Swift_Message($email->getSubject());
        $message->setFrom($this->getOfficialEmail($email->getSender()));
        $message->setTo([$email->getRecipientEmail() => $email->getRecipientName()]);

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
     * Replace recipient-related variables with their values in some input text.
     *
     * @param string The text to prepare.
     * @param User|Reviewer The recipient.
     * @return string
     */
    private function prepareRecipientText(string $text, $recipient): string
    {
        $text = preg_replace('/{{ ?email ?}}/', $recipient->getEmail(), $text);
        $text = preg_replace('/{{ ?firstname ?}}/', $recipient->getFirstname(), $text);
        $text = preg_replace('/{{ ?lastname ?}}/', $recipient->getLastname(), $text);
        return $text;
    }

    /**
     * Replace submission-related variables with their values in some input text.
     *
     * @param string The text to prepare.
     * @param Submission The submission.
     * @return string
     */
    private function prepareSubmissionText(string $text, Submission $submission): string
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
     * Replace review-related variables with their values in some input text.
     *
     * @param string The text to prepare.
     * @param Review The review.
     * @return string
     */
    private function prepareReviewText(string $text, Review $review): string
    {
        $text = $this->prepareSubmissionText($text, $review->getSubmission());
        $link = "https://www.humesociety.org/review/{$review->getLink()}";
        $text = preg_replace('/{{ ?link ?}}/', "<a href=\"{$link}\">{$link}</a>", $text);
        return $text;
    }

    /**
     * Replace recipient-related variables with their values.
     *
     * @param Email The email.
     * @param User|Reviewer The recipient.
     * @return void
     */
    private function prepareRecipientContent(Email $email, $recipient)
    {
        $email->setSubject($this->prepareRecipientText($email->getSubject(), $recipient));
        $email->setContent($this->prepareRecipientText($email->getContent(), $recipient));
    }

    /**
     * Replace submission-related variables with their values.
     *
     * @param Email The email.
     * @param Submission The submission.
     * @return void
     */
    public function prepareSubmissionContent(Email $email, Submission $submission)
    {
        $email->setSubject($this->prepareSubmissionText($email->getSubject(), $submission));
        $email->setContent($this->prepareSubmissionText($email->getContent(), $submission));
    }

    /**
     * Replace review-related variables with their values.
     *
     * @param Email The email.
     * @param Review The review.
     * @return void
     */
    public function prepareReviewContent(Email $email, Review $review)
    {
        $email->setSubject($this->prepareReviewText($email->getSubject(), $review));
        $email->setContent($this->prepareReviewText($email->getContent(), $review));
    }

    /**
     * Send an email to all members.
     *
     * @param bool Whether to include current members.
     * @param bool Whether to include lapsed members.
     * @param bool Whether to include members who have declined to recieve general emails.
     * @param Email The email to send.
     * @return Object
     */
    public function sendMembershipEmail(bool $current, bool $lapsed, bool $declining, Email $email): array
    {
        // get the recipients of the email
        $recipientUsers = [];
        if ($current) {
            foreach ($this->users->getMembersInGoodStanding() as $user) {
                if ($declining || $user->getReceiveEmail()) {
                    $recipientUsers[] = $user;
                }
            }
        }
        if ($lapsed) {
            foreach ($this->users->getMembersInArrears() as $user) {
                if ($declining || $user->getReceiveEmail()) {
                    $recipientUsers[] = $user;
                }
            }
        }

        // if there's an attachment, save it
        if ($email->getAttachment()) {
            $pathToAttachment = $this->attachmentsDir.$email->getAttachment()->getClientOriginalName();
            $email->getAttachment()->move($this->attachmentsDir, $email->getAttachment()->getClientOriginalName());
        } else {
            $pathToAttachment = null;
        }

        // send the email to each recipient, keeping track of the results
        $emailsSent = 0;
        $goodRecipients = [];
        $badRecipients = [];
        foreach ($recipientUsers as $user) {
            $emailToSend = clone $email;
            $emailToSend->setRecipientName($user->getFullname())
                ->setRecipientEmail($user->getEmail());
            $this->prepareRecipientContent($emailToSend, $user);
            $sent = $this->sendEmail($emailToSend, $pathToAttachment);
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
     * Create an email from a template saved in the database.
     *
     * @param string The label of the template.
     * @return Email|null
     */
    private function emailFromTemplate(string $label): ?Email
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

    /**
     * Send society email from template.
     *
     * @param User The recipient of the email.
     * @param string The label of the template to use.
     * @return void
     */
    public function sendSocietyEmail(User $user, string $label)
    {
        $email = $this->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($user);
            $this->prepareRecipientContent($email, $user);
            $this->sendEmail($email);
        }
    }

    /**
     * Send submission email from template.
     *
     * @param Submission The submission concerned.
     * @param string The label of the template to use.
     * @return void
     */
    public function sendSubmissionEmail(Submission $submission, string $label)
    {
        $email = $this->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($submission->getUser());
            $this->prepareRecipientContent($email, $submission->getUser());
            $this->prepareSubmissionContent($email, $submission);
            $this->sendEmail($email);
        }
    }

    /**
     * Send review email from template.
     *
     * @param Review The review concerned.
     * @param string The label of the template to use.
     * @return void
     */
    public function sendReviewEmail(Review $review, string $label)
    {
        $email = $this->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($review->getReviewer());
            $this->prepareRecipientContent($email, $review->getReviewer());
            $this->prepareReviewContent($email, $review);
            $this->sendEmail($email);
        }
    }

    /**
     * Send forgotten credentials email.
     *
     * @param User The recipient of the email.
     * @return void
     */
    public function sendForgotCredentialsEmail(User $user)
    {
        $email = new Email();
        $email->setSubject('Hume Society Login Credentials')
            ->setSender('web')
            ->setRecipient($user)
            ->setTemplate('forgot-credentials')
            ->addTwig('user', $user);
        $this->sendEmail($email);
    }

    /**
     * Send submission notification email to conference organisers.
     *
     * @param Submission The submission.
     * @return void
     */
    public function sendSubmissionNotification(Submission $submission)
    {
        $conference = $this->conferences->getCurrentConference();
        if ($conference) {
            $email = new Email();
            $email->setSubject("Submission to the {$conference}")
                ->setSender('web')
                ->setRecipientName('Conference Organisers')
                ->setRecipientEmail('conference@humesociety.org')
                ->setTemplate('submission-received')
                ->addTwig('conference', $conference)
                ->addTwig('submission', $submission);
            $this->sendEmail($email);
        }
    }

    /**
     * Send review acceptance/rejection notification email to conference organisers.
     *
     * @param Review The review.
     * @return void
     */
    public function sendReviewAcceptanceNotification(Review $review)
    {
        $conference = $this->conferences->getCurrentConference();
        if ($conference) {
            $email = new Email();
            if ($review->isAccepted()) {
                $email->setSubject('Review Invitation Accepted')
                    ->setTemplate('review-accepted');
            } else {
                $email->setSubject('Review Invitation Declined')
                    ->setTemplate('review-declined');
            }
            $email->setSender('web')
                ->setRecipientName('Conference Organisers')
                ->setRecipientEmail('conference@humesociety.org')
                ->addTwig('conference', $conference)
                ->addTwig('review', $review);
            $this->sendEmail($email);
        }
    }

    /**
     * Send review submission notification email to conference organisers.
     *
     * @param Review The review.
     * @return void
     */
    public function sendReviewSubmissionNotification(Review $review)
    {
        $conference = $this->conferences->getCurrentConference();
        if ($conference) {
            $email = new Email();
            $email->setSubject('Review Submitted')
                ->setSender('web')
                ->setRecipientName('Conference Organisers')
                ->setRecipientEmail('conference@humesociety.org')
                ->setTemplate('review-submitted')
                ->addTwig('conference', $conference)
                ->addTwig('review', $review);
            $this->sendEmail($email);
        }
    }
}
