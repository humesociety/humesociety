<?php

namespace App\Entity\Email;

use App\Entity\Chair\Chair;
use App\Entity\Comment\Comment;
use App\Entity\Conference\Conference;
use App\Entity\Paper\Paper;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * An email. Emails are created on the fly and not persisted to the database.
 */
class Email
{
    /**
     * The sender of the email.
     *
     * @var string
     */
    private $sender;

    /**
     * The recipient user.
     *
     * @var User
     */
    private $recipient;

    /**
     * The subject of the email.
     *
     * @var string
     */
    private $subject;

    /**
     * An attached file.
     *
     * @var UploadedFile|null
     */
    private $attachment;

    /**
     * Associative array of twig variables for use when rendering the email (includes email content).
     *
     * @var Object
     */
    private $twigs;

    /**
     * The twig template to use when rendering the email.
     *
     * Note that this is not the same as the email template entity from which the email was (possibly)
     * created; email template entities contain base content and other properties (sender, subject, etc.)
     * that are independent of the twig template used to render the content of the email itself.
     *
     * @var string
     */
    private $template;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        $this->sender = null;
        $this->recipient = null;
        $this->subject = null;
        $this->attachment = null;
        $this->twigs = ['content' => null];
        $this->template = 'base';
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->subject ? $this->subject : 'uninitialised email';
    }

    /**
     * Get the sender of the email (null when the object is created).
     *
     * @return string|null
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * Set the sender of the email.
     *
     * @param string The sender of the email.
     * @return self
     */
    public function setSender(string $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Get the recipient (null when the object is created).
     *
     * @return User|null
     */
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    /**
     * Set the recipient.
     *
     * @param User The recipient.
     * @return self
     */
    public function setRecipient(User $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * Get the subject of the email (null when the object is created).
     *
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Set the subject of the email.
     *
     * @param string The subject of the email.
     * @return self
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get the attached file.
     *
     * @return UploadedFile|null
     */
    public function getAttachment(): ?UploadedFile
    {
        return $this->attachment;
    }

    /**
     * Set the attached file.
     *
     * @param UploadedFile|null The attached file.
     * @return self
     */
    public function setAttachment(?UploadedFile $attachment): self
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * Get the twig variables.
     *
     * @return Object
     */
    public function getTwigs(): array
    {
        return $this->twigs;
    }

    /**
     * Add a twig variable.
     *
     * @param string The name of the variable.
     * @param mixed The value of the variable.
     * @return void
     */
    public function addTwig(string $name, $value): self
    {
        $this->twigs[$name] = $value;
        return $this;
    }

    /**
     * Get the content of the email (null when the object is created).
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->twigs['content'];
    }

    /**
     * Set the content of the email.
     *
     * @param string The content of the email.
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->twigs['content'] = $content;
        return $this;
    }

    /**
     * Get the twig template to use when rendering the email.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Set the twig template to use when rendering the email.
     *
     * @param string The twig template to use when rendering the email.
     * @return self
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Replace recipient-related variables with their values in the email's subject and content.
     *
     * @return self
     */
    public function prepareRecipientContent(): self
    {
        $username = $this->recipient->getUsername();
        $email = $this->recipient->getEmail();
        $firstname = $this->recipient->getFirstname();
        $lastname = $this->recipient->getLastname();
        $this->subject = preg_replace('/{{ ?username ?}}/', $username, $this->subject);
        $this->subject = preg_replace('/{{ ?email ?}}/', $email, $this->subject);
        $this->subject = preg_replace('/{{ ?firstname ?}}/', $firstname, $this->subject);
        $this->subject = preg_replace('/{{ ?lastname ?}}/', $lastname, $this->subject);
        $this->twigs['content'] = preg_replace('/{{ ?username ?}}/', $username, $this->twigs['content']);
        $this->twigs['content'] = preg_replace('/{{ ?email ?}}/', $email, $this->twigs['content']);
        $this->twigs['content'] = preg_replace('/{{ ?firstname ?}}/', $firstname, $this->twigs['content']);
        $this->twigs['content'] = preg_replace('/{{ ?lastname ?}}/', $lastname, $this->twigs['content']);
        return $this;
    }

    /**
     * Replace conference-related variables with their values in the email's subject and content.
     *
     * @param Conference The conference.
     * @param object Associative array of country codes and names.
     * @return self
     */
    public function prepareConferenceContent(Conference $conference, array $countries): self
    {
        $this->prepareRecipientContent();
        $ordinal = $conference->getOrdinal();
        $town = $conference->getTown();
        $country = $countries[$conference->getCountry()];
        $this->subject = preg_replace('/{{ ?ordinal ?}}/', $ordinal, $this->subject);
        $this->subject = preg_replace('/{{ ?town ?}}/', $town, $this->subject);
        $this->subject = preg_replace('/{{ ?country ?}}/', $country, $this->subject);
        $this->twigs['content'] = preg_replace('/{{ ?ordinal ?}}/', $ordinal, $this->twigs['content']);
        $this->twigs['content'] = preg_replace('/{{ ?town ?}}/', $town, $this->twigs['content']);
        $this->twigs['content'] = preg_replace('/{{ ?country ?}}/', $country, $this->twigs['content']);
        return $this;
    }

    /**
     * Replace submission-related variables with their values in the email's subject and content.
     *
     * @param Submission The submission.
     * @param object Associative array of country codes and names.
     * @return string
     */
    public function prepareSubmissionContent(Submission $submission, array $countries): self
    {
        $this->prepareConferenceContent($submission->getConference(), $countries);
        $title = $submission->getTitle();
        $abstract = $submission->getAbstract();
        $authors = $submission->getAuthors();
        $this->subject = preg_replace('/{{ ?title ?}}/', $title, $this->subject);
        $this->subject = preg_replace('/{{ ?authors ?}}/', $authors, $this->subject);
        $this->subject = preg_replace('/{{ ?abstract ?}}/', $abstract, $this->subject);
        $this->twigs['content'] = preg_replace('/{{ ?title ?}}/', $title, $this->twigs['content']);
        $this->twigs['content'] = preg_replace('/{{ ?authors ?}}/', $authors, $this->twigs['content']);
        $this->twigs['content'] = preg_replace('/{{ ?abstract ?}}/', $abstract, $this->twigs['content']);
        return $this;
    }

    /**
     * Replace review-related variables with their values in the email's subject and content.
     *
     * @param Review The review.
     * @param object Associative array of country codes and names.
     * @return string
     */
    public function prepareReviewContent(Review $review, array $countries): self
    {
        $this->prepareSubmissionContent($review->getSubmission(), $countries);
        $link = "https://www.humesociety.org/invitation/review/{$review->getSecret()}";
        $link = "<a href=\"{$link}\">{$link}</a>";
        $this->subject = preg_replace('/{{ ?link ?}}/', $link, $this->subject);
        $this->twigs['content'] = preg_replace('/{{ ?link ?}}/', $link, $this->twigs['content']);
        return $this;
    }

    /**
     * Replace comment-related variables with their values in the email's subject and content.
     *
     * @param Comment The comment.
     * @param object Associative array of country codes and names.
     * @return string
     */
    public function prepareCommentContent(Comment $comment, array $countries): self
    {
        $this->prepareSubmissionContent($comment->getSubmission(), $countries);
        $link = "https://www.humesociety.org/invitation/comment/{$comment->getSecret()}";
        $link = "<a href=\"{$link}\">{$link}</a>";
        $this->subject = preg_replace('/{{ ?link ?}}/', $link, $this->subject);
        $this->twigs['content'] = preg_replace('/{{ ?link ?}}/', $link, $this->twigs['content']);
        return $this;
    }

    /**
     * Replace chair-related variables with their values in the email's subject and content.
     *
     * @param Chair The chair.
     * @param object Associative array of country codes and names.
     * @return string
     */
    public function prepareChairContent(Chair $chair, array $countries): self
    {
        $this->prepareSubmissionContent($chair->getSubmission(), $countries);
        $link = "https://www.humesociety.org/invitation/chair/{$chair->getSecret()}";
        $link = "<a href=\"{$link}\">{$link}</a>";
        $this->subject = preg_replace('/{{ ?link ?}}/', $link, $this->subject);
        $this->twigs['content'] = preg_replace('/{{ ?link ?}}/', $link, $this->twigs['content']);
        return $this;
    }

    /**
     * Replace paper-related variables with their values in the email's subject and content.
     *
     * @param Paper The paper.
     * @param object Associative array of country codes and names.
     * @return string
     */
    public function preparePaperContent(Paper $paper, array $countries): self
    {
        $this->prepareConferenceContent($paper->getConference(), $countries);
        $link = "https://www.humesociety.org/invitation/paper/{$paper->getSecret()}";
        $link = "<a href=\"{$link}\">{$link}</a>";
        $this->subject = preg_replace('/{{ ?link ?}}/', $link, $this->subject);
        $this->twigs['content'] = preg_replace('/{{ ?link ?}}/', $link, $this->twigs['content']);
        return $this;
    }
}
