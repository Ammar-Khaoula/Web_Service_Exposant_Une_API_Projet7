<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        for ($i = 0; $i < 50; $i++) {
            $product = new Product;
            $product->setName('phone ' . $i);
            $product->setDescription('description phone : ' . $i);
            $product->setQuantity(mt_rand(2, 4));
            $product->setPrice(mt_rand(799, 1200));
            $product->setModel('model phone : ' . $i);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
