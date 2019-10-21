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
        $this->conferenceOrganisers = new User();
        $this->conferenceOrganisers->setFirstname('Conference')
            ->setLastname('Organisers')
            ->setEmail('conference@humesociety.org');
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
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('submission-received')
            ->addTwig('submission', $submission);
        $this->emails->sendEmail($email);
    }

    /**
     * Send submission final version notification email to conference organisers.
     *
     * @param Submission The submission.
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
     * @param Review The review.
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
     * @param Review The review.
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
     * @param Comment The comment.
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
     * @param Comment The comment.
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
     * @param Chair The chair invitation.
     * @return void
     */
    public function sendChairResponseNotification(Chair $chair)
    {
        $email = new Email();
        if ($chair->isAccepted()) {
            $email->setSubject("{$chair->getSubmission()->getConference()}: Chair Invitation Accepted")
                ->addTwig('response', 'accepted');
        } else {
            $email->setSubject("{$comment->getSubmission()->getConference()}: Chair Invitation Declined")
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
     * @param Paper The paper.
     * @return void
     */
    public function sendPaperSubmissionNotification(Paper $paper)
    {
        $email = new Email();
        $email->setSubject("{$paper->getConference()}: Paper Received")
            ->setSender('web')
            ->setRecipient($this->conferenceOrganisers)
            ->setTemplate('paper-recieved')
            ->addTwig('paper', $paper);
        $this->emails->sendEmail($email);
    }
}
