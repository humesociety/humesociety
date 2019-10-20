<?php

namespace App\Entity\Email;

use App\Entity\Review\Review;
use App\Entity\Paper\Paper;
use App\Entity\Submission\Submission;
use App\Entity\User\User;

/**
 * The system email handler contains methods for sending SYSTEM emails.
 */
class SystemEmailHandler
{
    /**
     * The (main) email handler
     *
     * @var EmailHandler
     */
    private $emails;

    /**
     * Constructor function.
     *
     * @param EmailHandler The (main) email handler.
     * @return void
     */
    public function __construct(EmailHandler $emails)
    {
        $this->emails = $emails;
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
        $this->emails->sendEmail($email);
    }

    /**
     * Send submission notification email to conference organisers.
     *
     * @param Submission The submission.
     * @return void
     */
    public function sendSubmissionNotification(Submission $submission)
    {
        $email = new Email();
        $email->setSubject("{$submission->getConference()}: Paper Submitted")
            ->setSender('web')
            ->setRecipientName('Conference Organisers')
            ->setRecipientEmail('conference@humesociety.org')
            ->setTemplate('submission-received')
            ->addTwig('submission', $submission);
        $this->emails->sendEmail($email);
    }

    /**
     * Send review acceptance/rejection notification email to conference organisers.
     *
     * @param Review The review.
     * @return void
     */
    public function sendReviewAcceptanceNotification(Review $review)
    {
        $email = new Email();
        if ($review->isAccepted()) {
            $email->setSubject("{$review->getSubmission()->getConference()}: Review Invitation Accepted")
                ->setTemplate('review-accepted');
        } else {
            $email->setSubject("{$review->getSubmission()->getConference()}: Review Invitation Declined")
                ->setTemplate('review-declined');
        }
        $email->setSender('web')
            ->setRecipientName('Conference Organisers')
            ->setRecipientEmail('conference@humesociety.org')
            ->addTwig('review', $review);
        $this->emails->sendEmail($email);
    }

    /**
     * Send review submission notification email to conference organisers.
     *
     * @param Review The review.
     * @return void
     */
    public function sendReviewSubmissionNotification(Review $review)
    {
        $email = new Email();
        $email->setSubject("{$review->getSubmission()->getConference()}: Review Submitted")
            ->setSender('web')
            ->setRecipientName('Conference Organisers')
            ->setRecipientEmail('conference@humesociety.org')
            ->setTemplate('review-submitted')
            ->addTwig('review', $review);
        $this->emails->sendEmail($email);
    }

    /**
     * Send invited paper submission notification email to conference organisers.
     *
     * @param Paper The paper.
     * @return void
     */
    public function sendPaperSubmissionNotification(Paper $paper)
    {
        $email = new Email();
        $email->setSubject("{$paper->getConference()}: Paper Submitted")
            ->setSender('web')
            ->setRecipientName('Conference Organisers')
            ->setRecipientEmail('conference@humesociety.org')
            ->setTemplate('paper-submitted')
            ->addTwig('paper', $paper);
        $this->emails->sendEmail($email);
    }
}
