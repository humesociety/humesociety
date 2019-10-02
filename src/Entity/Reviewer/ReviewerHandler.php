<?php

namespace App\Entity\Reviewer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The review handler contains the main business logic for reading and writing review data.
 */
class ReviewerHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The reviewer repository.
     *
     * @var ReviewerRepository
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
        $this->repository = $manager->getRepository(Reviewer::class);
    }

    /**
     * Get all reviewers from the database.
     *
     * @return Reviewer[]
     */
    public function getReviewers(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Save/update a reviewer in the database.
     *
     * @param Reviewer The reviewer to save/update.
     */
    public function saveReviewer(Reviewer $reviewer)
    {
        $this->manager->persist($reviewer);
        $this->manager->flush();
    }

    /**
     * Delete a reviewer from the database.
     *
     * @param Reviewer The reviewer to delete.
     */
    public function deleteReviewer(Review $reviewer)
    {
        $this->manager->remove($reviewer);
        $this->manager->flush();
    }
}
