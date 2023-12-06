<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        for ($i = 1; $i < 20; $i++) {
            $user = new User;
            $user->setFirstName('prenomUser : ' . $i);
            $user->setLastName('nomUser : ' . $i);
            $user->setEmail('emailUser : ' . $i);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
