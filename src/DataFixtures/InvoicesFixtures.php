<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InvoicesFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $invoice = (new Invoice())
                ->setAmount(mt_rand(20000, 200000))
                ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                ->setDescription($faker->catchPhrase);

            $manager->persist($invoice);
        }

        $manager->flush();
    }
}
