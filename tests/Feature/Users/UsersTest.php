<?php

namespace App\Tests\Feature\Users;

use App\Repository\UserRepository;
use App\Tests\ApiTestCase;

class UsersTest extends ApiTestCase
{
    /** @test */
    public function it_can_register_new_users()
    {
        // Given we have some user's data
        $userData = [
            'email' => 'user@mail.com',
            'fullName' => 'John Doe',
            'plainPassword' => 'p4ssword'
        ];

        // When we call /api/users with POST method
        static::jsonRequest('POST', '/api/users', $userData);

        // Then the response is 201
        static::assertResponseStatusCodeSame(201);
        static::assertJsonResponse();

        // And it contains the new user's data
        $json = static::getJsonResponseData();
        static::assertEquals($userData['email'], $json->email);
        static::assertEquals($userData['fullName'], $json->fullName);

        // And the user is in the database 
        $user = static::$container->get(UserRepository::class)->findOneBy(['email' => 'user@mail.com']);
        static::assertNotNull($user);
        static::assertEquals($userData['fullName'], $user->getFullName());

        // And he has an hashed password
        static::assertNotEquals($userData['plainPassword'], $user->getPassword());
    }

    /** @test */
    public function it_can_edit_the_authenticated_user()
    {
        // Given we have a user in the database
        $user = static::actAsAuthenticated();

        // And an updated data
        $updatedData = [
            'fullName' => 'MOCK_UPDATED_FULLNAME',
            'email' => 'updated@mail.com'
        ];

        // When we call /api/users/{id} with method PUT
        static::jsonRequest('PUT', '/api/users/' . $user->getId(), $updatedData);

        // Then the response should be successfull and JSON
        static::assertResponseIsSuccessful();
        static::assertJsonResponse();

        // And it should contains the updated data
        $json = static::getJsonResponseData();
        static::assertEquals($updatedData['fullName'], $json->fullName);
        static::assertEquals($updatedData['email'], $json->email);

        // And the user should also have been updated in the database
        $updatedUser = static::$container->get(UserRepository::class)->find($user->getId());
        static::assertEquals($updatedData['fullName'], $updatedUser->getFullName());
        static::assertEquals($updatedData['email'], $updatedUser->getEmail());
    }

    /** @test */
    public function it_can_remove_a_user_as_the_same_authenticated_user()
    {
        // Given we have a user in the database
        $user = static::actAsAuthenticated();

        $userId = $user->getId();

        // When we call /api/users/{id} with method DELETE
        static::jsonRequest('DELETE', '/api/users/' . $userId);

        // Then the response should be successfull
        static::assertResponseIsSuccessful();

        // And the user should not be in the database anymore
        $deletedUser = static::getUserById($userId);
        static::assertNull($deletedUser);
    }
}
