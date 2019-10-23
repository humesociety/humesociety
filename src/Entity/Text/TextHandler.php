<?php

namespace App\Entity\Text;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
     * @var EntityRepository
     */
    private $repository;

    /**
     * The conference texts (from `services.yml`).
     *
     * @var Object
     */
    private $conferenceTexts;

    /**
     * Conference text group ids (from `services.yml`).
     *
     * @var object
     */
    private $conferenceTextGroupIds;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface $manager The Doctrine entity manager.
     * @param ParameterBagInterface $params Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, ParameterBagInterface $params)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Text::class);
        $this->conferenceTexts = $params->get('conference_texts');
        $this->conferenceTextGroupIds = explode('|', $params->get('conference_text_group_ids'));
    }

    /**
     * Enrich a text object with its title and description from the `services.yml` file.
     *
     * @param Text The text object to enrich.
     * @return Text
     */
    private function enrichText(Text $text): Text
    {
        $baseText = $this->conferenceTexts[$text->getLabel()];
        $text->setGroup($baseText['group']);
        $text->setTitle($baseText['title']);
        $text->setDescription($baseText['description']);
        return $text;
    }

    /**
     * Get a text variable from its label. Create it if it doesn't already exist.
     *
     * @param string $label The text variable's label.
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
     * @param string $label The text variable's label.
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
     * Get conference text variables.
     *
     * @return Text[]
     */
    public function getConferenceTextGroups(): array
    {
        $conferenceTextGroups = [];
        foreach ($this->conferenceTextGroupIds as $group) {
            $conferenceTextGroups[$group] = [];
        }
        foreach ($this->getConferenceTexts() as $text) {
            $conferenceTextGroups[$text->getGroup()][] = $text;
        }
        return $conferenceTextGroups;
    }

    /**
     * Save/update some text in the database.
     *
     * @param Text $text The text to save/update.
     * @return void
     */
    public function saveText(Text $text)
    {
        $this->manager->persist($text);
        $this->manager->flush();
    }
}
