<?php

namespace App\Entity\Text;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The text handler contains the main business logic for reading and writing text data.
 */
class TextHandler
{
    /**
     * The Doctrine entity manager (dependency injection).
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The text repository (dependency injection).
     *
     * @var TextRepository
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
        $this->repository = $manager->getRepository(Text::class);
    }

    /**
     * Get some text from its label.
     *
     * @param string The label.
     * @return Text|null
     */
    public function getTextByLabel(string $label): ?Text
    {
        return $this->repository->findOneByLabel($label);
    }

    /**
     * Get some text content from its label.
     *
     * @param string The label.
     * @return string|null
     */
    public function getTextContentByLabel(string $label): ?string
    {
        $text = $this->repository->findOneByLabel($label);

        return $text ? $text->getContent() : null;
    }

    /**
     * Save/update some text in the database.
     *
     * @param Text The text to save/update.
     */
    public function saveText(Text $text)
    {
        $this->manager->persist($text);
        $this->manager->flush();
    }

    /**
     * Delete some text from the database.
     *
     * @param Text The text to delete.
     */
    public function deleteText(Text $text)
    {
        $this->manager->remove($text);
        $this->manager->flush();
    }
}
