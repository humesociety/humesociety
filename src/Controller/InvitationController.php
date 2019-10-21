<?php

namespace App\Controller;

use App\Entity\Chair\Chair;
use App\Entity\Chair\ChairHandler;
use App\Entity\Comment\Comment;
use App\Entity\Comment\CommentHandler;
use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\SystemEmailHandler;
use App\Entity\Paper\Paper;
use App\Entity\Paper\PaperHandler;
use App\Entity\Paper\PaperType;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewHandler;
use App\Entity\Review\ReviewType;
use App\Entity\Text\TextHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for responding to invitations (to review, comment, chair, or submit a paper).
 *
 * @Route("/invitation", name="invitation_")
 */
class InvitationController extends AbstractController
{
    /**
     * Route for handling a review invitation.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param ReviewHandler The review handler.
     * @param TextHandler The text handler.
     * @param string The review's secret.
     * @return Response
     * @Route("/review/{secret}/{reply}", name="review", requirements={"reply": "accept|decline"})
     */
    public function review(
        Request $request,
        ConferenceHandler $conferences,
        SystemEmailHandler $systemEmails,
        ReviewHandler $reviews,
        TextHandler $texts,
        string $secret,
        ?string $reply = null
    ): Response {
        // look for the review
        $review = $reviews->getReviewBySecret($secret);

        // throw 404 error if not found
        if (!$review) {
            throw $this->createNotFoundException('Page not found.');
        }

        // throw 404 error if the review isn't for the current conference
        if ($review->getSubmission()->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // maybe handle the reply
        if ($review->getStatus() === 'pending' && $reply) {
            $review->setAccepted($reply === 'accept');
            $reviews->saveReview($review);
            $systemEmails->sendReviewResponseNotification($review);
        }

        // initialise the twig variables
        $twigs = [
            'page' => [
                'slug' => 'review',
                'section' => 'review',
                'title' => "Review for {$review->getSubmission()}"
            ],
            'review' => $review
        ];

        // return a different response depending on the review's status
        switch ($review->getStatus()) {
            case 'pending':
                // render and return the page
                return $this->render('site/invitation/review/decide.twig', $twigs);

            case 'accepted':
                // create and handle the review form
                $reviewForm = $this->createForm(ReviewType::class, $review);
                $reviewForm->handleRequest($request);
                if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
                    // submit the review
                    $review->setDateSubmitted(new \DateTime('today'));
                    $reviews->saveReview($review);
                    $systemEmails->sendReviewSubmissionNotification($review);

                    // add the thank you message to the twig variables
                    $twigs['reviewThanks'] = $texts->getTextContentByLabel('review-acknowledgement');

                    // render and return the page
                    return $this->render('site/invitation/review/submitted.twig', $twigs);
                }

                // add the review guidance text and form to the twig variables
                $twigs['reviewGuidance'] = $texts->getTextContentByLabel('review-guidance');
                $twigs['reviewForm'] = $reviewForm->createView();

                // render and return the page
                return $this->render('site/invitation/review/submit.twig', $twigs);

            case 'declined':
                // render and return the page
                return $this->render('site/invitation/review/declined.twig', $twigs);

            case 'submitted':
                // add the thank you message to the twig variables
                $twigs['reviewThanks'] = $texts->getTextContentByLabel('review-acknowledgement');

                // render and return the page
                return $this->render('site/invitation/review/submitted.twig', $twigs);
        }
    }

    /**
     * Route for handling a comment invitation.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param CommentHandler The comment handler.
     * @param TextHandler The text handler.
     * @param string The comment's secret.
     * @return Response
     * @Route("/comment/{secret}/{reply}", name="comment", requirements={"reply": "accept|decline"})
     */
    public function comment(
        Request $request,
        ConferenceHandler $conferences,
        SystemEmailHandler $systemEmails,
        CommentHandler $comments,
        TextHandler $texts,
        string $secret,
        ?string $reply = null
    ): Response {
        // look for the comment
        $comment = $comments->getCommentBySecret($secret);

        // throw 404 error if not found
        if (!$comment) {
            throw $this->createNotFoundException('Page not found.');
        }

        // throw 404 error if the comment isn't for the current conference
        if ($comment->getSubmission()->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // maybe handle the reply
        if ($comment->getStatus() === 'pending' && $reply) {
            $comment->setAccepted($reply === 'accept');
            $comments->saveComment($comment);
            $systemEmails->sendCommentResponseNotification($comment);
        }

        // initialise the twig variables
        $twigs = [
            'page' => [
                'slug' => 'review',
                'section' => 'review',
                'title' => "Comments for {$comment->getSubmission()}"
            ],
            'comment' => $comment
        ];

        // return a different response depending on the comment's status
        switch ($comment->getStatus()) {
            case 'pending':
                // render and return the page
                return $this->render('site/invitation/comment/decide.twig', $twigs);

            case 'accepted':
                // create and handle the comment form
                $commentForm = $this->createForm(CommentType::class, $comment);
                $commentForm->handleRequest($request);
                if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                    // submit the comment
                    $comment->setDateSubmitted();
                    $comments->saveComment($comment);
                    $systemEmails->sendCommentSubmissionNotification($comment);

                    // add the thank you message to the twig variables
                    $twigs['commentThanks'] = $texts->getTextContentByLabel('comment-acknowledgement');

                    // render and return the page
                    return $this->render('site/invitation/comment/submitted.twig', $twigs);
                }

                // add the comment guidance text and form to the twig variables
                $twigs['commentGuidance'] = $texts->getTextContentByLabel('comment-guidance');
                $twigs['commentForm'] = $commentForm->createView();

                // render and return the page
                return $this->render('site/invitation/comment/submit.twig', $twigs);

            case 'declined':
                // render and return the page
                return $this->render('site/invitation/comment/declined.twig', $twigs);

            case 'submitted':
                // add the thank you message to the twig variables
                $twigs['commentThanks'] = $texts->getTextContentByLabel('comment-acknowledgement');

                // render and return the page
                return $this->render('site/invitation/comment/submitted.twig', $twigs);
        }
    }

    /**
     * Route for handling a chair invitation.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param CommentHandler The comment handler.
     * @param TextHandler The text handler.
     * @param string The comment's secret.
     * @return Response
     * @Route("/chair/{secret}/{reply}", name="chair", requirements={"reply": "accept|decline"})
     */
    public function chair(
        Request $request,
        ConferenceHandler $conferences,
        SystemEmailHandler $systemEmails,
        ChairHandler $comments,
        TextHandler $texts,
        string $secret,
        ?string $reply = null
    ): Response {
        // look for the chair invitation
        $chair = $chair->getChairBySecret($secret);

        // throw 404 error if not found
        if (!$chair) {
            throw $this->createNotFoundException('Page not found.');
        }

        // throw 404 error if the chair invitation isn't for the current conference
        if ($chair->getSubmission()->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // maybe handle the reply
        if ($chair->getStatus() === 'pending' && $reply) {
            $chair->setAccepted($reply === 'accept');
            $chair->saveComment($chair);
            $systemEmails->sendChairResponseNotification($chair);
        }

        // initialise the twig variables
        $twigs = [
            'page' => [
                'slug' => 'review',
                'section' => 'review',
                'title' => "Chair Invitation for {$chair->getSubmission()}"
            ],
            'chair' => $chair
        ];

        // return a different response depending on the comment's status
        switch ($comment->getStatus()) {
            case 'pending':
                // add the chair guidance text to the twig variables
                $twigs['chairGuidance'] = $texts->getTextContentByLabel('chair-guidance');

                // render and return the page
                return $this->render('site/invitation/chair/decide.twig', $twigs);

            case 'accepted':
                // add the thank you message to the twig variables
                $twigs['chairThanks'] = $texts->getTextContentByLabel('chair-acknowledgement');

                // render and return the page
                return $this->render('site/invitation/chair/accepted.twig', $twigs);

            case 'declined':
                // render and return the page
                return $this->render('site/invitation/chair/declined.twig', $twigs);
        }
    }

    /**
     * Route for handling a paper invitation.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param ConferenceEmailHandler The conference email handler.
     * @param PaperHandler The paper handler.
     * @param TextHandler The text handler.
     * @param string The paper's secret.
     * @return Response
     * @Route("/paper/{secret}", name="paper")
     */
    public function paper(
        Request $request,
        ConferenceHandler $conferences,
        SystemEmailHandler $systemEmails,
        PaperHandler $papers,
        TextHandler $texts,
        string $secret,
        ?string $reply = null
    ): Response {
        // look for the paper
        $paper = $papers->getPaperBySecret($secret);

        // throw 404 error if not found
        if (!$paper) {
            throw $this->createNotFoundException('Page not found.');
        }

        // throw 404 error if the paper isn't for the current conference
        if ($paper->getConference() !== $conferences->getCurrentConference()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // initialise the twig variables
        $twigs = [
            'page' => [
                'slug' => 'review',
                'section' => 'review',
                'title' => "Paper for the {$paper->getConference()}"
            ],
            'paper' => $paper,
            'conference' => $paper->getConference()
        ];

        // return a different response depending on the paper's status
        switch ($paper->getStatus()) {
            case 'pending':
                // create and handle the paper form
                $paperForm = $this->createForm(PaperType::class, $paper);
                $paperForm->handleRequest($request);
                if ($paperForm->isSubmitted() && $paperForm->isValid()) {
                    // submit the paper
                    $paper->setAccepted(true);
                    $paper->setDateSubmitted();
                    $papers->savePaper($paper);
                    $systemEmails->sendPaperSubmissionNotification($paper);

                    // add the thank you message to the twig variables
                    $twigs['paperThanks'] = $texts->getTextContentByLabel('paper-acknowledgement');

                    // render and return the page
                    return $this->render('site/invitation/paper/submitted.twig', $twigs);
                }

                // add the paper guidance text and form to the twig variables
                $twigs['paperGuidance'] = $texts->getTextContentByLabel('paper-guidance');
                $twigs['paperForm'] = $paperForm->createView();

                // render and return the page
                return $this->render('site/invitation/paper/submit.twig', $twigs);

            case 'submitted':
                // add the thank you message to the twig variables
                $twigs['paperThanks'] = $texts->getTextContentByLabel('paper-acknowledgement');

                // render and return the page
                return $this->render('site/invitation/paper/submitted.twig', $twigs);
        }
    }
}
