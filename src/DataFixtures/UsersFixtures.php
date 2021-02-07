<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UsersFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $manager->persist((new User)
                ->setEmail("admin@mail.com")
                ->setPlainPassword("p4ssword")
                ->setFullName("admin")
                ->setRoles(['ROLE_ADMIN'])
        );

        $manager->persist((new User)
            ->setEmail("jerome@mail.com")
            ->setPlainPassword("p4ssword")
            ->setFullName("Jérome Dupont"));

        $manager->persist((new User)
            ->setEmail("anne@mail.com")
            ->setPlainPassword("p4ssword")
            ->setFullName("Anne Durand"));

        $manager->flush();
    }
}
