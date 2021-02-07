<?php

namespace App\Tests\Integration;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UsersTest extends KernelTestCase
{
    /** @test */
    public function it_can_save_a_new_user_and_hash_its_password()
    {
        static::bootKernel();

        $user = (new User)
            ->setFullName("Lior Chamla")
            ->setEmail("lior@mail.com")
            ->setPlainPassword("p4ssword");

        static::$container->get(EntityManagerInterface::class)->persist($user);
        static::$container->get(EntityManagerInterface::class)->flush();

        static::assertNotNull($user->getId());
        static::assertNotSame("p4ssword", $user->getPassword());
        static::assertNull($user->getPlainPassword());
    }
}
