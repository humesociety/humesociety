<?php

namespace App\Service;

use App\Entity\Text\Text;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The text manager contains the main business logic for reading and writing text data.
 */
class TextManager
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The text repository.
     *
     * @var TextRepository
     */
    private $repository;

    /**
     * The conference texts (as defined in `services.yml`).
     *
     * @var Object
     */
    private $conferenceTexts;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
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
    private function enrich(Text $text): Text
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
            $text = new Text();
            $text->setLabel($label);
        }
        return $this->enrich($text);
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
     * Save/update a text variable.
     *
     * @param Text The text variable to save/update.
     */
    public function saveText(Text $text)
    {
        $this->manager->persist($text);
        $this->manager->flush();
    }

    /**
     * Delete a text variable.
     *
     * @param Text The text variable to delete.
     */
    public function deleteText(Text $text)
    {
        $this->manager->remove($text);
        $this->manager->flush();
    }
}
