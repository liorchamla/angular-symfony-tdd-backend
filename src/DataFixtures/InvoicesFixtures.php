<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class InvoicesFixtures extends Fixture implements DependentFixtureInterface
{

    protected UserRepository $userRepository;
    protected ObjectManager $manager;
    protected Generator $faker;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->faker = Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $jerome = $this->userRepository->findOneBy(['email' => 'jerome@mail.com']);
        $anne = $this->userRepository->findOneBy(['email' => 'anne@mail.com']);

        $this->createInvoicesForUser(30, $jerome);
        $this->createInvoicesForUser(50, $anne);

        $manager->flush();
    }

    protected function createInvoicesForUser(int $count, User $user): void
    {
        for ($i = 0; $i < $count; $i++) {
            $invoice = (new Invoice())
                ->setCreatedAt($this->faker->dateTimeBetween('-6 months'))
                ->setDescription($this->faker->catchPhrase)
                ->setUser($user);

            for ($l = 0; $l < mt_rand(1, 10); $l++) {
                $line = (new InvoiceLine)
                    ->setDescription($this->faker->catchPhrase)
                    ->setAmount(mt_rand(20000, 200000));

                $invoice->addLine($line);
            }

            $this->manager->persist($invoice);
        }
    }
}
