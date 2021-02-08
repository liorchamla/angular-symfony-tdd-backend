<?php

namespace App\Tests\Feature\Users;

use App\Tests\ApiTestCase;

class UsersSecurityTest extends ApiTestCase
{

    /** @test */
    public function it_cant_edit_a_user_as_anonymous()
    {
        $user = static::getRandomUser();

        // And an updated data
        $updatedData = [
            'fullName' => 'MOCK_UPDATED_FULLNAME',
            'email' => 'updated@mail.com'
        ];

        // When we call /api/users/{id} with method PUT
        static::jsonRequest('PUT', '/api/users/' . $user->getId(), $updatedData);

        static::assertResponseStatusCodeSame(401);
    }

    /** @test */
    public function it_cant_edit_a_user_as_an_other_authenticated_user()
    {

        $anne = static::getUserByEmail('anne@mail.com');
        $jerome = static::getUserByEmail('jerome@mail.com');

        static::actAsAuthenticated($jerome);

        // And an updated data
        $updatedData = [
            'fullName' => 'MOCK_UPDATED_FULLNAME',
            'email' => 'updated@mail.com'
        ];

        // When we call /api/users/{id} with method PUT
        static::jsonRequest('PUT', '/api/users/' . $anne->getId(), $updatedData);

        static::assertResponseStatusCodeSame(404);
    }

    /** @test */
    public function it_cant_remove_a_user_as_anonymous()
    {
        // Given we have a user in the database
        $user = static::getRandomUser();

        $userId = $user->getId();

        // When we call /api/users/{id} with method DELETE
        static::jsonRequest('DELETE', '/api/users/' . $userId);

        // Then the response should be 401 Forbidden
        static::assertResponseStatusCodeSame(401);

        // And the user should not be in the database anymore
        $deletedUser = static::getUserById($userId);
        static::assertNotNull($deletedUser);
    }

    /** @test */
    public function it_cant_remove_a_user_as_an_other_authenticated_user()
    {
        // Given we have a user in the database
        $anne = static::getUserByEmail('anne@mail.com');
        $jerome = static::getUserByEmail('jerome@mail.com');

        static::actAsAuthenticated($jerome);

        $userId = $anne->getId();

        // When we call /api/users/{id} with method DELETE
        static::jsonRequest('DELETE', '/api/users/' . $userId);

        // Then the response should be 404 Not Found
        static::assertResponseStatusCodeSame(404);

        // And the user should not be in the database anymore
        $deletedUser = static::getUserById($userId);
        static::assertNotNull($deletedUser);
    }
}
