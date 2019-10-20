<?php

namespace App\Entity\EmailTemplate;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The email template handler contains the main business logig for reading and writing email tempate data.
 */
class EmailTemplateHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The email template repository.
     *
     * @var EmailTemplateRepository
     */
    private $repository;

    /**
     * Email template data (from `services.yml`).
     *
     * @var object
     */
    private $emailTemplates;

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
        $this->repository = $manager->getRepository(EmailTemplate::class);
        $this->emailTemplates = $params->get('email_templates');
        $this->conferenceEmailTemplateGroupIds = explode('|', $params->get('conference_email_template_group_ids'));
    }

    /**
     * Enrich an email template with its title and description (from `services.yml`).
     *
     * @param EmailTemplate The email template to enrich.
     * @return EmailTemplate
     */
    private function enrichEmailTemplate(EmailTemplate $emailTemplate): EmailTemplate
    {
        $template = $this->emailTemplates[$emailTemplate->getLabel()];
        $emailTemplate->setGroup($template['group']);
        $emailTemplate->setTitle($template['title']);
        $emailTemplate->setDescription($template['description']);
        if ($emailTemplate->getSender() === null) {
            // set the default sender, only if the sender hasn't been set already
            $emailTemplate->setSender($template['sender']);
        }
        return $emailTemplate;
    }

    /**
     * Get an (enriched) email template from its label. Create it if it doesn't exist.
     *
     * @param string The label of the template to get.
     * @return EmailTemplate
     */
    public function getEmailTemplateByLabel(string $label): EmailTemplate
    {
        $emailTemplate = $this->repository->findOneByLabel($label);
        if (!$emailTemplate) {
            $emailTemplate = new EmailTemplate();
            $emailTemplate->setLabel($label);
        }
        return $this->enrichEmailTemplate($emailTemplate);
    }

    /**
     * Get email templates.
     *
     * @param string|null Optional group to restrict to.
     * @return EmailTemplate[]
     */
    public function getEmailTemplates(?string $group = null): array
    {
        $emailTemplates = ($group === null)
            ? $this->emailTemplates
            : array_filter($this->emailTemplates, function ($emailTemplate) use ($group) {
                return $emailTemplate['group'] === $group;
            });
        return array_map('self::getEmailTemplateByLabel', array_keys($emailTemplates));
    }

    /**
     * Get all groups of conference email templates
     *
     * @return Object
     */
    public function getConferenceEmailTemplateGroups(): array
    {
        $conferenceEmailTemplateGroups = [];
        foreach ($this->conferenceEmailTemplateGroupIds as $group) {
            $conferenceEmailTemplateGroups[$group] = $this->getEmailTemplates($group);
        }
        return $conferenceEmailTemplateGroups;
    }

    /**
     * Save/update an email template.
     *
     * @param EmailTemplate The email template to save/update.
     * @return void
     */
    public function saveEmailTemplate(EmailTemplate $emailTemplate)
    {
        $this->manager->persist($emailTemplate);
        $this->manager->flush();
    }
}
