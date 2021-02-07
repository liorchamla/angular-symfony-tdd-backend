<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
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
                ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                ->setDescription($faker->catchPhrase);

            for ($l = 0; $l < mt_rand(1, 10); $l++) {
                $line = (new InvoiceLine)
                    ->setDescription($faker->catchPhrase)
                    ->setAmount(mt_rand(20000, 200000));

                $invoice->addLine($line);
            }

            $manager->persist($invoice);
        }

        $manager->flush();
    }
}
