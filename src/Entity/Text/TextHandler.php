<?php

namespace App\Entity\Text;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
     * The conference texts (from `services.yml`).
     *
     * @var Object
     */
    private $conferenceTexts;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param ParameterBagInterface Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, ParameterBagInterface $params)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Text::class);
        $this->conferenceTexts = $params->get('conference_texts');
    }

    /**
     * Enrich a text object with its title and description from the `services.yml` file.
     *
     * @param Text The text object to enrich.
     * @return Text
     */
    private function enrichText(Text $text): Text
    {
        $text->setTitle($this->conferenceTexts[$text->getLabel()]['title']);
        $text->setDescription($this->conferenceTexts[$text->getLabel()]['description']);
        return $text;
    }

    /**
     * Get a text variable from its label. Create it if it doesn't already exist.
     *
     * @param string The label.
     * @return Text
     */
    public function getTextByLabel(string $label): Text
    {
        $text = $this->repository->findOneByLabel($label);
        if (!$text) {
            $text = new Text($label);
        }
        return $this->enrichText($text);
    }

    /**
     * Get the content of a text variable from its label.
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
     * Get conference text variables.
     *
     * @return Text[]
     */
    public function getConferenceTexts(): array
    {
        return array_map('self::getTextByLabel', array_keys($this->conferenceTexts));
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
}
