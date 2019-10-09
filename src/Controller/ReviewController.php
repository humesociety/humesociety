<?php

namespace App\Controller;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Email\EmailHandler;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewHandler;
use App\Entity\Review\ReviewType;
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
     * @param ConferenceManager The conference manager.
     * @param EmailHandler The email handler.
     * @param ReviewHandler The review handler.
     * @param string The reviewer's secret.
     * @param string The review's secret.
     * @return Response
     * @Route("/{reviewerSecret}/{reviewSecret}", name="index")
     */
    public function index(
        Request $request,
        ConferenceManager $conferences,
        EmailHandler $emails,
        ReviewHandler $reviews,
        string $reviewerSecret,
        string $reviewSecret
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

        // initialise the twig variables
        $twigs = ['review' => $review];

        // return a different response depending on the review's status
        switch ($review->getStatus()) {
            case 'pending':
                // render and return the page
                return $this->render('site/review/accept-reject.twig', $twigs);

            case 'accepted':
                // create and handle the review form
                $reviewForm = $this->createForm(ReviewType::class, $review);
                $twigs['reviewForm'] = $reviewForm->createView();
                $reviewForm->handleRequest($request);
                if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
                    $review->setDateSubmitted(new \DateTime());
                    $reviews->saveReview($review);
                    $emails->sendReviewSubmissionNotification($review);
                    return $this->redirectToRoute('review_index', [
                        'reviewerSecret' => $review->getReviewer()->getSecret(),
                        'reviewSecret' => $review->getSecret()
                    ]);
                }

                // add the review instructions to the twig variables
                $twigs['reviewInstructions'] = $conferences->getTextByLabel('review');

                // render and return the page
                return $this->render('site/review/submit.twig', $twigs);

            case 'declined':
                // render and return the page
                return $this->render('site/review/declined.twig', $twigs);

            case 'submitted':
                // add the thank you message to the twig variables
                $twigs['reviewThanks'] = $conferences->getTextByLabel('thanks');

                // render and return the page
                return $this->render('site/review/submitted.twig', $twigs);
        }
    }

    /**
     * Route for accepting or declining an invitation to review.
     *
     * @param ConferenceHandler The conference handler.
     * @param EmailHandler The email handler.
     * @param ReviewHandler The review handler.
     * @param string The reviewer's secret.
     * @param string The review's secret.
     * @param bool Whether the invitation is accepted or declined.
     * @return Response
     * @Route("/accept/{reviewerSecret}/{reviewSecret}/{accepted}", name="accept_or_decline")
     */
    public function acceptOrDecline(
        ConferenceHandler $conferences,
        EmailHandler $emails,
        ReviewHandler $reviews,
        string $reviewerSecret,
        string $reviewSecret,
        string $accepted
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

        // mark the review as accepted/declined and email the conference organisers
        $review->setAccepted($accepted == '1');
        $reviews->saveReview($review);
        $emails->sendReviewAcceptanceNotification($review);

        // redirect to the review's main page
        return $this->redirectToRoute('review_index', [
            'reviewerSecret' => $review->getReviewer()->getSecret(),
            'reviewSecret' => $review->getSecret()
        ]);
    }
}
