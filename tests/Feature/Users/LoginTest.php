<?php

namespace App\Tests\Feature\Users;

use App\Repository\UserRepository;
use App\Tests\ApiTestCase;

class LoginTest extends ApiTestCase
{
    /** @test */
    public function it_can_login_existing_user()
    {
        // Given we have a user in the database
        $user = static::$container->get(UserRepository::class)->findOneBy([]);

        // And same login data
        $loginData = [
            'email' => $user->getEmail(),
            'password' => 'p4ssword'
        ];

        // When we call /api/login with POST method
        static::jsonRequest('POST', '/api/login', $loginData);

        // Then the response should be successfull and contain JSON
        static::assertResponseIsSuccessful();
        static::assertJsonResponse();

        // And it contains a token field 
        $json = static::getJsonResponseData();
        static::assertNotNull($json->token);
    }

    /** @test */
    public function it_sends_all_wanted_data_inside_jwt_token()
    {
        // Given we have a user (admin@mail.com)
        $user = self::$container->get(UserRepository::class)->findOneBy(['email' => 'admin@mail.com']);

        // And we setup it's data to login
        $userData = [
            'email' => $user->getEmail(),
            'password' => 'p4ssword'
        ];

        // When we call /api/login with POST method and data
        self::jsonRequest('POST', '/api/login', $userData);

        // Then we should find a JWT Token
        $json = static::getJsonResponseData();
        static::assertNotNull($json->token);

        // And it should contain all the wanted data
        $data = self::$container->get('lexik_jwt_authentication.encoder.lcobucci')->decode($json->token);

        static::assertEquals($user->getFullName(), $data['fullName']);
        static::assertEquals($user->getEmail(), $data['email']);
        static::assertEquals($user->getRoles(), $data['roles']);
    }

    /** 
     * @test
     * @dataProvider provideBadCredentials
     */
    public function it_cant_login_an_invalid_credentials_user($email, $password)
    {
        // Given we have some invalid user data
        $userData = [
            'email' => $email,
            'password' => $password
        ];

        // When we call /api/login in POST method with the invalid credentials
        static::jsonRequest('POST', '/api/login', $userData);

        // Then the response should be a 401
        static::assertResponseStatusCodeSame(401);
        static::assertJsonResponse();

        // And it should contain a message : "Impossible de se connecter avec ces informations"
        $json = static::getJsonResponseData();
        static::assertEquals("Impossible de se connecter avec ces informations", $json->message);
    }

    public function provideBadCredentials()
    {
        return [
            ['invalid@mail.com', 'invalid_password'],
            ['jerome@mail.com', 'invalid_password'],
            ['invalid@mail.com', ''],
            ['', ''],
        ];
    }
}
