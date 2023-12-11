<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UserFixtures extends Fixture
{
    private $PasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $PasswordHasher)
    {
        $this->PasswordHasher = $PasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        // Création d'un client "normal"
        $customer = new Customer();
        $customer->setEmail("customer@gmail.com");
        $customer->setName("khaoula");
        $customer->setPassword($this->PasswordHasher->hashPassword($customer, "123456"));
        
        $listCustomer = [];
        for($i = 1; $i < 10; $i++){
            $customer = new Customer();
            $customer->setName($faker->company());
            $customer->setEmail($faker->freeEmail());
            $customer->setPassword($this->PasswordHasher->hashPassword($customer, "password"));
            
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
