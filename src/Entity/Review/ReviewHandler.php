<?php

namespace App\Entity\Review;

use App\Entity\Conference\Conference;
use App\Entity\Reviewer\Reviewer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The review handler contains the main business logic for reading and writing review data.
 */
class ReviewHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The review repository.
     *
     * @var ReviewRepository
     */
    private $repository;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Review::class);
    }

    /**
     * Get all review invitations.
     *
     * @param Conference|null Optional conference to restrict to.
     * @return Review[]
     */
    public function getReviews(?Conference $conference = null): array
    {
        $reviews = $this->repository->findAll();
        if ($conference === null) {
            return $comments;
        }
        return array_filter($reviews, function ($review) use ($conference) {
            return $review->getSubmission()->getConference() === $conference;
        });
    }

    /**
     * Get a review from its secret.
     *
     * @param string The review secret.
     * @return Review|null
     */
    public function getReviewBySecret(string $secret): ?Review
    {
        return $this->repository->findOneBySecret($secret);
    }

    /**
     * Save/update a review in the database.
     *
     * @param Review The review to save/update.
     */
    public function saveReview(Review $review)
    {
        $this->manager->persist($review);
        $this->manager->flush();
    }
}
