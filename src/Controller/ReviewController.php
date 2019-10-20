<?php

namespace App\Controller;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\EmailHandler;
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
 * Controller for submitting reviews.
 *
 * @Route("/review", name="review_")
 */
class ReviewController extends AbstractController
{
    /**
     * Route for handling a review.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param EmailHandler The email handler.
     * @param ReviewHandler The review handler.
     * @param TextHandler The text handler.
     * @param string The reviewer's secret.
     * @param string The review's secret.
     * @return Response
     * @Route("/{reviewerSecret}/{reviewSecret}/{reply}", name="index", requirements={"reply": "accept|decline"})
     */
    public function index(
        Request $request,
        ConferenceHandler $conferences,
        EmailHandler $emails,
        ReviewHandler $reviews,
        TextHandler $texts,
        string $reviewerSecret,
        string $reviewSecret,
        ?string $reply = null
    ): Response {
        // look for the review
        $review = $reviews->getReviewBySecret($reviewSecret);

        // return 404 if not found
        if (!$review) {
            throw $this->createNotFoundException('Page not found.');
        }

        // return 404 if reviewer secret doesn't match
        if ($review->getReviewer()->getSecret() !== $reviewerSecret) {
            throw $this->createNotFoundException('Page not found.');
        }

        // maybe handle the reviewer's reply
        if ($review->getStatus() === 'pending' && $reply) {
            $review->setAccepted($reply === 'accept');
            $reviews->saveReview($review);
            $emails->sendReviewAcceptanceNotification($review);
        }

        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'review', 'section' => 'review', 'title' => "Review for “{$review->getSubmission()}”"],
            'review' => $review
        ];

        // return a different response depending on the review's status
        switch ($review->getStatus()) {
            case 'pending':
                // render and return the page
                return $this->render('site/review/decide.twig', $twigs);

            case 'accepted':
                // create and handle the review form
                $reviewForm = $this->createForm(ReviewType::class, $review);
                $twigs['reviewForm'] = $reviewForm->createView();
                $reviewForm->handleRequest($request);
                if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
                    // submit the review
                    $review->setDateSubmitted(new \DateTime('today'));
                    $reviews->saveReview($review);
                    $emails->sendReviewSubmissionNotification($review);

                    // add the thank you message to the twig variables
                    $twigs['reviewThanks'] = $texts->getTextContentByLabel('thanks');

                    // render and return the page
                    return $this->render('site/review/submitted.twig', $twigs);
                }

                // add the review instructions to the twig variables
                $twigs['reviewInstructions'] = $texts->getTextContentByLabel('review');

                // render and return the page
                return $this->render('site/review/submit.twig', $twigs);

            case 'declined':
                // render and return the page
                return $this->render('site/review/declined.twig', $twigs);

            case 'submitted':
                // add the thank you message to the twig variables
                $twigs['reviewThanks'] = $texts->getTextContentByLabel('thanks');

                // render and return the page
                return $this->render('site/review/submitted.twig', $twigs);
        }
    }
}
