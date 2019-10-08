<?php

namespace App\Service;

use App\Entity\Conference\Conference;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The emailer service, for sending emails.
 */
class Emailer
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
     * Replace review-related variables in email text.
     *
     * @param string The email text.
     * @param Review The review.
     * @return string
     */
    private function prepareReviewContent(string $text, Review $review): string
    {
        $text = $this->prepareSubmissionContent($text, $review->getSubmission());
        $text = preg_replace('/{{ ?link ?}}/', $review->getLink(), $text);
        return $text;
    }

    /**
     * Send an email.
     *
     * @param Email The email to send.
     * @return void
     */
    private function sendEmail(Email $email)
    {
        // create the email
        $message = new \Swift_Message($email->getSubject());
        $email->addTwig('image', $message->embed(\Swift_Image::fromPath($this->projectDir.'/public/logo.jpg')));
        $message->setFrom($this->userHandler->getOfficialEmail($email->getSender()));
        $message->setTo([$email->getRecipient()->getEmail() => $email->getRecipient()->getFullname()]);
        $message->setBody($this->templating->render('email/'.$email->getTemplate().'.twig', $email->getTwigs()), 'text/html');
        if ($email->getPathToAttachment) {
            $message->attach(\Swift_Attachment::fromPath($pathToAttachment));
        }

        // send the email
        return $this->mailer->send($message);
    }

    /**
     * Send an email to all members.
     *
     * @param bool Whether to include current members.
     * @param bool Whether to include lapsed members.
     * @param bool Whether to include members who have declined to recieve general emails.
     * @param Email The email to send.
     * @param string The subject of the email.
     * @param string The content of the email.
     * @param string|null The path to any attachment.
     * @return object
     */
    public function sendMembershipEmail(bool $current, bool $lapsed, bool $declining, Email $email): array
    {
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
     * Send society email from template.
     *
     * @param User The recipient of the email.
     * @param string The template to use.
     * @return void
     */
    public function sendSocietyEmail(User $user, string $template)
    {
        $email = $this->emailFromTemplate($template);
        if ($email) {
            $this->prepareSocietyEmail($email, $user);
            $this->sendEmail($email);
        }
    }

    /**
     * Send submission email from template.
     *
     * @param Submission The submission concerned.
     * @param string The template to use.
     * @return void
     */
    public function sendSubmissionEmail(Submission $submission, string $template)
    {
        $email = $this->emailFromTemplate($template);
        if ($email) {
            $this->prepareSubmissionEmail($email, $submission);
            $this->sendEmail($email);
        }
    }

    /**
     * Send review email from template.
     *
     * @param Review The review concerned.
     * @param string The template to use.
     * @return void
     */
    public function sendReviewEmail(Review $review, string $template)
    {
        $email = $this->emailFromTemplate($template);
        if ($email) {
            $this->prepareReviewEmail($email, $review);
            $this->sendEmail($email);
        }
    }

    /**
     * Send password reset email.
     *
     * @param User The recipient of the email.
     * @return void
     */
    public function sendResetPasswordEmail(User $user)
    {
        $email = new Email();
        $email->setSubject('Hume Society Password Reset')
            ->setSender('web')
            ->setRecipient($user)
            ->setTemplate('reset-password')
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
            $email->setSubject('Submission to the '.$conference)
                ->setSender('web')
                ->setRecipient($this->userHandler->getOfficialEmail('conference')) /// WRONG
                ->setTemplate('submission-received')
                ->addTwig('conference', $conference)
                ->addTwig('submission', $submission);
            $this->sendEmail($email);
        }
    }
}
