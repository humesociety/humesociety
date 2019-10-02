<?php

namespace App\Entity\Review;

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
     * Save/update a review in the database.
     *
     * @param Review The review to save/update.
     */
    public function saveReview(Review $review)
    {
        $this->manager->persist($review);
        $this->manager->flush();
    }

    /**
     * Delete a review from the database.
     *
     * @param Review The review to delete.
     */
    public function deleteReview(Review $review)
    {
        $this->manager->remove($review);
        $this->manager->flush();
    }
}
