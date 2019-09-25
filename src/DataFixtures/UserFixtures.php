<?php

namespace App\DataFixtures;

use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('tech')
             ->addRole('ROLE_MEMBER')
             ->addRole('ROLE_TECH')
             ->setEmail('web@humesociety.org')
             ->setFirstname('Technical')
             ->setLastname('Director')
             ->setDues(1);
        $encodedPassword = $this->encoder->encodePassword($user, 'password');
        $user->setPassword($encodedPassword);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('evpt')
             ->addRole('ROLE_MEMBER')
             ->addRole('ROLE_EVPT')
             ->setEmail('vicepresident@humesociety.org')
             ->setFirstname('Vice')
             ->setLastname('President')
             ->setDues(1);
        $encodedPassword = $this->encoder->encodePassword($user, 'password');
        $user->setPassword($encodedPassword);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('organiser')
             ->addRole('ROLE_MEMBER')
             ->addRole('ROLE_ORGANISER')
             ->setEmail('conference@humesociety.org')
             ->setFirstname('Conference')
             ->setLastname('Organiser')
             ->setDues(1);
        $encodedPassword = $this->encoder->encodePassword($user, 'password');
        $user->setPassword($encodedPassword);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('editor')
             ->addRole('ROLE_MEMBER')
             ->addRole('ROLE_EDITOR')
             ->setEmail('editors@humestudies.org')
             ->setFirstname('Journal')
             ->setLastname('Editor')
             ->setDues(1);
        $encodedPassword = $this->encoder->encodePassword($user, 'password');
        $user->setPassword($encodedPassword);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('member')
             ->addRole('ROLE_MEMBER')
             ->setEmail('member@humesociety.org')
             ->setFirstname('Society')
             ->setLastname('Member')
             ->setDues(1);
        $encodedPassword = $this->encoder->encodePassword($user, 'password');
        $user->setPassword($encodedPassword);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('lapsed_member')
             ->addRole('ROLE_MEMBER')
             ->setEmail('lapsed_member@humesociety.org')
             ->setFirstname('Lapsed')
             ->setLastname('Member')
             ->setDues(0);
        $encodedPassword = $this->encoder->encodePassword($user, 'password');
        $user->setPassword($encodedPassword);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('non_member')
             ->setEmail('non_member@humesociety.org')
             ->setFirstname('Non')
             ->setLastname('Member');
        $encodedPassword = $this->encoder->encodePassword($user, 'password');
        $user->setPassword($encodedPassword);
        $manager->persist($user);

        $manager->flush();
    }
}
