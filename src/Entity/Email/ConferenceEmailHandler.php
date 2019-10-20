<?php

namespace App\Entity\Email;

use App\Entity\Chair\Chair;
use App\Entity\Chair\ChairHandler;
use App\Entity\Comment\Comment;
use App\Entity\Comment\CommentHandler;
use App\Entity\Paper\Paper;
use App\Entity\Paper\PaperHandler;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewHandler;
use App\Entity\Submission\Submission;
use App\Entity\Submission\SubmissionHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The conference email handler contains methods for sending CONFERENCE emails.
 */
class ConferenceEmailHandler
{
    /**
     * The (main) email handler.
     *
     * @var EmailHandler
     */
    private $emails;

    /**
     * The chair handler.
     *
     * @var ChairHandler
     */
    private $chairs;

    /**
     * The comment handler.
     *
     * @var CommentHandler
     */
    private $comments;

    /**
     * The paper handler.
     *
     * @var PaperHandler
     */
    private $papers;

    /**
     * The review handler.
     *
     * @var ReviewHandler
     */
    private $reviews;

    /**
     * The submission handler.
     *
     * @var SubmissionHandler
     */
    private $submissions;

    /**
     * An associative array of countries and their country codes.
     *
     * @var object
     */
    private $countries;

    /**
     * Constructor function.
     *
     * @param EmailHandler The (main) email handler.
     * @param SubmissionHandler The submission handler.
     * @param ParameterBagInterface Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(
        EmailHandler $emails,
        ChairHandler $chairs,
        CommentHandler $comments,
        PaperHandler $papers,
        ReviewHandler $reviews,
        SubmissionHandler $submissions,
        ParameterBagInterface $params
    ) {
        $this->emails = $emails;
        $this->chairs = $chairs;
        $this->comments = $comments;
        $this->papers = $papers;
        $this->reviews = $reviews;
        $this->submissions = $submissions;
        $this->countries = $params->get('countries');
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
        $email = $this->emails->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($submission->getUser());
            $email->prepareSubmissionContent($submission, $this->countries);
            $this->emails->sendEmail($email);
            switch ($label) {
                case 'submission-acceptance': // fallthrough
                case 'submission-rejection':
                    $submission->setDateDecisionEmailed();
                    $this->submissions->saveSubmission($submission);
                    break;
                case 'submission-reminder':
                    $submission->incrementSubmissionReminderEmails();
                    $this->submissions->saveSubmission($submission);
                    break;
            }
        } else {
            throw new \Error("Email template '{$label}' not found.");
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
        $email = $this->emails->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($review->getUser());
            $email->prepareReviewContent($review, $this->countries);
            $this->emails->sendEmail($email);
            switch ($label) {
                case 'review-invitation-reminder':
                    $review->incrementInvitationReminderEmails();
                    $this->reviews->saveReview($review);
                    break;

                case 'review-submission-reminder':
                    $review->incrementSubmissionReminderEmails();
                    $this->reviews->saveReview($review);
                    break;
            }
        } else {
            throw new \Error("Email template '{$label}' not found.");
        }
    }

    /**
     * Send comment email from template.
     *
     * @param Comment The comment concerned.
     * @param string The label of the template to use.
     * @return void
     */
    public function sendCommentEmail(Comment $comment, string $label)
    {
        $email = $this->emails->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($comment->getUser());
            $email->prepareCommentContent($comment, $this->countries);
            $this->emails->sendEmail($email);
            switch ($label) {
                case 'comment-invitation-reminder':
                    $comment->incrementInvitationReminderEmails();
                    $this->comments->saveComment($comment);
                    break;

                case 'review-submission-reminder':
                    $comment->incrementSubmissionReminderEmails();
                    $this->comments->saveComment($comment);
                    break;
            }
        } else {
            throw new \Error("Email template '{$label}' not found.");
        }
    }

    /**
     * Send chair email from template.
     *
     * @param Chair The chair concerned.
     * @param string The label of the template to use.
     * @return void
     */
    public function sendChairEmail(Chair $chair, string $label)
    {
        $email = $this->emails->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($chair->getUser());
            $email->prepareChairContent($chair, $this->countries);
            $this->emails->sendEmail($email);
            switch ($label) {
                case 'chair-invitation-reminder':
                    $chair->incrementInvitationReminderEmails();
                    $this->chairs->saveChair($chair);
                    break;
            }
        } else {
            throw new \Error("Email template '{$label}' not found.");
        }
    }

    /**
     * Send paper email from template.
     *
     * @param Paper The paper concerned.
     * @param string The label of the template to use.
     * @return void
     */
    public function sendPaperEmail(Paper $paper, string $label)
    {
        $email = $this->emails->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($paper->getUser());
            $email->preparePaperContent($paper, $this->countries);
            $this->emails->sendEmail($email);
            switch ($label) {
                case 'paper-invitation-reminder':
                    $paper->incrementInvitationReminderEmails();
                    $this->papers->savePaper($paper);
                    break;
            }
        } else {
            throw new \Error("Email template '{$label}' not found.");
        }
    }
}
