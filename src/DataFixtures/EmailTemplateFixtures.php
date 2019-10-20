<?php

namespace App\DataFixtures;

use App\Entity\EmailTemplate\EmailTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class EmailTemplateFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // society emails
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('welcome')
            ->setSender('vicepresident')
            ->setSubject('welcome')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('reminder')
            ->setSender('vicepresident')
            ->setSubject('submission accepted')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        // submission-related emails
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('submission-acknowledgement')
            ->setSender('conference')
            ->setSubject('submission received')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('submission-acceptance')
            ->setSender('conference')
            ->setSubject('submission accepted')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('submission-rejection')
            ->setSender('conference')
            ->setSubject('submission rejected')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('submission-reminder')
            ->setSender('conference')
            ->setSubject('submission reminder')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('submission-comments-submitted')
            ->setSender('conference')
            ->setSubject('comments submitted')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        // review-related emails
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('review-invitation')
            ->setSender('conference')
            ->setSubject('review invitation')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('review-invitation-reminder')
            ->setSender('conference')
            ->setSubject('review invitation reminder')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('review-submission-reminder')
            ->setSender('conference')
            ->setSubject('review submission reminder')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        // comment-related emails
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('comment-invitation')
            ->setSender('conference')
            ->setSubject('comment invitation')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('comment-invitation-reminder')
            ->setSender('conference')
            ->setSubject('comment invitation reminder')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('comment-paper-submitted')
            ->setSender('conference')
            ->setSubject('comment paper submitted')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('comment-submission-reminder')
            ->setSender('conference')
            ->setSubject('comment submission reminder')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        // chair-related emails
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('chair-invitation')
            ->setSender('conference')
            ->setSubject('chair invitation')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('chair-invitation-reminder')
            ->setSender('conference')
            ->setSubject('chair invitation reminder')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        // paper-related emails
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('paper-invitation')
            ->setSender('conference')
            ->setSubject('paper invitation')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setLabel('paper-invitation-reminder')
            ->setSender('conference')
            ->setSubject('paper invitation reminder')
            ->setContent('{{ firstname }} {{ lastname }}');
        $manager->persist($emailTemplate);

        $manager->flush();
    }
}
