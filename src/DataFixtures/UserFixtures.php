<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Customer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $listCustomer = [];
        for ($i = 1; $i < 5; $i++) {
            $customer = new Customer();
            $customer->setName('nomDeClient : ' . $i);
            $customer->setRole([$i]);
            $customer->setEmail('emailDeClient : ' . $i);
            $customer->setPassword('motDePasse : ' . $i);

            $manager->persist($customer);
            $listCustomer[] = $customer;
        }

        for ($i = 1; $i < 20; $i++) {
            $user = new User();
            $user->setFirstName('prenomUser : ' . $i);
            $user->setLastName('nomUser : ' . $i);
            $user->setEmail('emailUser : ' . $i);
            $user->setCustomer($listCustomer[array_rand($listCustomer)]);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
