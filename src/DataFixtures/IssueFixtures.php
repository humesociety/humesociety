<?php

namespace App\DataFixtures;

use App\Entity\Issue\Issue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class IssueFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $issue = new Issue();
        $issue->setVolume(1)
            ->setNumber(1)
            ->setMuseId(12345)
            ->setEditors('no one');
        $manager->persist($issue);

        $issue = new Issue();
        $issue->setVolume(1)
            ->setNumber(2)
            ->setMuseId(23456)
            ->setEditors('no one');
        $manager->persist($issue);

        $manager->flush();
    }
}
