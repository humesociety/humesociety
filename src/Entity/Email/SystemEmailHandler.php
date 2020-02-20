<?php

namespace App\Entity\Email;

use App\Entity\Chair\Chair;
use App\Entity\Comment\Comment;
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
     * Dummy user representing the conference organisers; to use as the recipient of the conference emails.
     *
     * @var User
     */
    private $conferenceOrganisers;

    /**
     * Constructor function.
     *
     * @param EmailHandler The (main) email handler.
     * @return void
     */
    public function __construct(EmailHandler $emails)
    {
        $this->emails = $emails;
        $this->conferenceOrganisers = new User();
        $this->conferenceOrganisers->setFirstname('Conference')
            ->setLastname('Organisers')
            ->setEmail('conference@humesociety.org');
    }

    /**
     * Send forgotten credentials email.
     *
     * @param User $user The recipient of the email.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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
     * @param Submission $submission The submission concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendSubmissionNotification(Submission $submission)
    {
        $email = new Email();
        $email->setSubject("{$submission->getConference()}: Paper Submitted")
            ->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('submission-received')
            ->addTwig('submission', $submission);
        $this->emails->sendEmail($email);
    }

    /**
     * Send submission confirmation notification email to conference organisers.
     *
     * @param Submission $submission The submission concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendSubmissionConfirmationNotification(Submission $submission)
    {
        $email = new Email();
        if ($submission->isConfirmed()) {
            $email->setSubject("{$submission->getConference()}: Attendance Confirmed");
        } else {
            $email->setSubject("{$submission->getConference()}: Attendance Declined");
        }
        $email->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('submission-confirmed')
            ->addTwig('submission', $submission);
        $this->emails->sendEmail($email);
    }

    /**
     * Send submission final version notification email to conference organisers.
     *
     * @param Submission $submission The submission concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendSubmissionFinalNotification(Submission $submission)
    {
        $email = new Email();
        $email->setSubject("{$submission->getConference()}: Final Version Submitted")
            ->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('submission-final-version-submitted')
            ->addTwig('submission', $submission);
        $this->emails->sendEmail($email);
    }

    /**
     * Send review invitation response notification email to conference organisers.
     *
     * @param Review $review The review invitation concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendReviewResponseNotification(Review $review)
    {
        $email = new Email();
        if ($review->isAccepted()) {
            $email->setSubject("{$review->getSubmission()->getConference()}: Review Invitation Accepted")
                ->addTwig('response', 'accepted');
        } else {
            $email->setSubject("{$review->getSubmission()->getConference()}: Review Invitation Declined")
                ->addTwig('response', 'declined');
        }
        $email->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('review-response')
            ->addTwig('review', $review);
        $this->emails->sendEmail($email);
    }

    /**
     * Send review submission notification email to conference organisers.
     *
     * @param Review $review The review invitation concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendReviewSubmissionNotification(Review $review)
    {
        $email = new Email();
        $email->setSubject("{$review->getSubmission()->getConference()}: Review Submitted")
            ->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('review-submitted')
            ->addTwig('review', $review);
        $this->emails->sendEmail($email);
    }

    /**
     * Send comment invitation response notification email to conference organisers.
     *
     * @param Comment $comment The comment invitation concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendCommentResponseNotification(Comment $comment)
    {
        $email = new Email();
        if ($comment->isAccepted()) {
            $email->setSubject("{$comment->getSubmission()->getConference()}: Comment Invitation Accepted")
                ->addTwig('response', 'accepted');
        } else {
            $email->setSubject("{$comment->getSubmission()->getConference()}: Comment Invitation Declined")
                ->addTwig('response', 'declined');
        }
        $email->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('comment-response')
            ->addTwig('comment', $comment);
        $this->emails->sendEmail($email);
    }

    /**
     * Send comment submission notification email to conference organisers.
     *
     * @param Comment $comment The comment invitation concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendCommentSubmissionNotification(Comment $comment)
    {
        $email = new Email();
        $email->setSubject("{$comment->getSubmission()->getConference()}: Comments Submitted")
            ->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('comment-submitted')
            ->addTwig('comment', $comment);
        $this->emails->sendEmail($email);
    }

    /**
     * Send chair invitation response notification email to conference organisers.
     *
     * @param Chair $chair The chair invitation concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendChairResponseNotification(Chair $chair)
    {
        $email = new Email();
        if ($chair->isAccepted()) {
            $email->setSubject("{$chair->getSubmission()->getConference()}: Chair Invitation Accepted")
                ->addTwig('response', 'accepted');
        } else {
            $email->setSubject("{$chair->getSubmission()->getConference()}: Chair Invitation Declined")
                ->addTwig('response', 'declined');
        }
        $email->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('chair-response')
            ->addTwig('chair', $chair);
        $this->emails->sendEmail($email);
    }

    /**
     * Send invited paper submission notification email to conference organisers.
     *
     * @param Paper $paper The paper invitation concerned.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @return void
     */
    public function sendPaperSubmissionNotification(Paper $paper)
    {
        $email = new Email();
        $email->setSubject("{$paper->getConference()}: Paper Received")
            ->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('paper-received')
            ->addTwig('paper', $paper);
        $this->emails->sendEmail($email);
    }
}
