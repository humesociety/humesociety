<?php

namespace App\DataFixtures;

use App\Entity\Page\Page;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PageFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $page = new Page();
        $page->setSection('about')
            ->setSlug('index')
            ->setPosition(1)
            ->setTemplate('default')
            ->setTitle('About the Hume Society')
            ->setContent('<p>About us.</p>');
        $manager->persist($page);

        $page = new Page();
        $page->setSection('conferences')
            ->setSlug('index')
            ->setPosition(1)
            ->setTemplate('default')
            ->setTitle('Conferences')
            ->setContent('<p>About our conferences.</p>');
        $manager->persist($page);

        $page = new Page();
        $page->setSection('hs')
            ->setSlug('index')
            ->setPosition(1)
            ->setTemplate('default')
            ->setTitle('Hume Studies')
            ->setContent('<p>About Hume Studies.</p>');
        $manager->persist($page);

        $page = new Page();
        $page->setSection('scholarship')
            ->setSlug('index')
            ->setPosition(1)
            ->setTemplate('default')
            ->setTitle('Hume Scholarship')
            ->setContent('<p>Some interesting stuff could go here.</p>');
        $manager->persist($page);

        $page = new Page();
        $page->setSection('members')
            ->setSlug('index')
            ->setPosition(1)
            ->setTemplate('default')
            ->setTitle('For Members')
            ->setContent('<p>This page is restricted.</p>');
        $manager->persist($page);

        $manager->flush();
    }
}
