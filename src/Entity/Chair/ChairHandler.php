<?php

namespace App\Entity\Chair;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The chair handler contains the main business logic for reading and writing chair data.
 */
class ChairHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The chair repository.
     *
     * @var ChairRepository
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
        $this->repository = $manager->getRepository(Chair::class);
    }

    /**
     * Save/update a chair.
     *
     * @param Chair The chair to save/update.
     */
    public function saveChair(Chair $chair)
    {
        $this->manager->persist($chair);
        $this->manager->flush();
    }

    /**
     * Delete a chair.
     *
     * @param Chair The chair to delete.
     */
    public function deleteChair(Chair $chair)
    {
        $this->manager->remove($chair);
        $this->manager->flush();
    }
}
