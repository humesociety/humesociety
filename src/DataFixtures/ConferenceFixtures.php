<?php

namespace App\DataFixtures;

use App\Entity\Conference\Conference;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ConferenceFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $conference = new Conference();
        $conference->setNumber(1)
            ->setYear(2020)
            ->setTown('Oxford')
            ->setCountry('GBR')
            ->setInstitution('University of Oxford');
        $manager->persist($conference);

        $manager->flush();
    }
}
