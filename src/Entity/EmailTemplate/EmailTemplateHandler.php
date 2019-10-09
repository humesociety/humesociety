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
     * Society email template data (from `services.yml`).
     *
     * @var object
     */
    private $societyEmailTemplates;

    /**
     * Conference email template data (from `services.yml`).
     *
     * @var object
     */
    private $conferenceEmailTemplates;

    /**
     * Society and conference email template data merged.
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
        $this->societyEmailTemplates = $params->get('society_email_templates');
        $this->conferenceEmailTemplates = $params->get('conference_email_templates');
        $this->emailTemplates = array_merge($this->societyEmailTemplates, $this->conferenceEmailTemplates);
    }

    /**
     * Enrich an email template with its title and description (from `services.yml`).
     *
     * @param EmailTemplate The email template to enrich.
     * @return EmailTemplate
     */
    private function enrichEmailTemplate(EmailTemplate $emailTemplate): EmailTemplate
    {
        $emailTemplate->setTitle($this->emailTemplates[$emailTemplate->getLabel()]['title']);
        $emailTemplate->setDescription($this->emailTemplates[$emailTemplate->getLabel()]['description']);
        return $emailTemplate;
    }

    /**
     * Get an email template from its label. Create it if it doesn't exist.
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
            if (in_array($label, array_keys($this->societyEmailTemplates))) {
                $emailTemplate->setSender('vicepresident');
            } elseif (in_array($label, array_keys($this->conferenceEmailTemplates))) {
                $emailTemplate->setSender('conference');
            }
        }
        return $this->enrichEmailTemplate($emailTemplate);
    }

    /**
     * Get society email templates.
     *
     * @return EmailTemplate[]
     */
    public function getSocietyEmailTemplates(): array
    {
        return array_map('self::getEmailTemplateByLabel', array_keys($this->societyEmailTemplates));
    }

    /**
     * Get conference email templates.
     *
     * @return EmailTemplate[]
     */
    public function getConferenceEmailTemplates(): array
    {
        return array_map('self::getEmailTemplateByLabel', array_keys($this->conferenceEmailTemplates));
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
