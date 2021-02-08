<?php

namespace App\Tests\Feature\Users;

use App\Tests\ApiTestCase;

class UsersValidationTest extends ApiTestCase
{

    /** @test */
    public function it_cant_register_a_user_if_email_is_already_taken()
    {
        // Given we have a user with email "jerome@mail.com" in the database
        // And an other user who wants to register the same email
        $data = [
            'email' => 'jerome@mail.com',
            'plainPassword' => 'password',
            'fullName' => 'Jérome Autre'
        ];

        // When we call /api/users with POST method
        static::jsonRequest('POST', '/api/users', $data);

        // Then the response should be 400 and contain JSON
        static::assertGreaterThanOrEqual(400, static::$client->getResponse()->getStatusCode());
        static::assertJsonResponse();

        // And contains "Vous ne pouvez pas créer un compte avec cette adresse email"
        static::assertStringContainsString("Vous ne pouvez pas créer un compte avec cette adresse email", static::$client->getResponse()->getContent());
    }

    /** 
     * @test 
     * @dataProvider provideInvalidRegistrationData
     */
    public function it_cant_create_a_user_if_data_is_invalid($data, $errors)
    {
        // Given we try to register with invalid data
        // When we call /api/users in POST 
        static::jsonRequest('POST', '/api/users', $data);

        // Then the response should be 400
        static::assertResponseStatusCodeSame(422);

        $json = static::getJsonResponseData();

        static::assertCount(count($errors), $json->violations);

        foreach ($errors as $field => $message) {
            $violation = static::getViolation($field);
            static::assertNotNull($violation);
            static::assertEquals($message, $violation->message);
        }
    }

    public function provideInvalidRegistrationData()
    {
        yield [
            [
                'fullName' => '',
                'email' => 'lior@mail.com',
                'plainPassword' => 'p4ssword'
            ],
            [
                'fullName' => "Le nom complet est obligatoire !"
            ]
        ];
        yield [
            [
                'fullName' => '',
                'email' => '',
                'plainPassword' => 'p4ssword'
            ],
            [
                'fullName' => "Le nom complet est obligatoire !",
                'email' => "L'adresse email est obligatoire !"
            ]
        ];
        yield [
            [
                'email' => 'liormail.com',
                'fullName' => '',
                'plainPassword' => '',
            ],
            [
                'fullName' => "Le nom complet est obligatoire !",
                'email' => "L'adresse email soumise n'est pas au format réglementaire",
                "plainPassword" =>  "Le mot de passe est obligatoire !"
            ]
        ];
        yield [
            [
                'fullName' => '',
                'email' => 'jerome@mail.com',
                'plainPassword' => ''
            ],
            [
                'fullName' => "Le nom complet est obligatoire !",
                'email' => "Vous ne pouvez pas créer un compte avec cette adresse email",
                "plainPassword" =>  "Le mot de passe est obligatoire !"
            ]
        ];
    }

    /** 
     * @test 
     * @dataProvider provideInvalidProfileData
     */
    public function it_cant_edit_a_user_if_data_is_invalid($data, $errors)
    {
        // Given we have a user in the database
        $user = static::actAsAuthenticated();

        // When we call /api/users in POST 
        static::jsonRequest('PUT', '/api/users/' . $user->getId(), $data);

        // Then the response should be 400
        static::assertResponseStatusCodeSame(422);

        $json = static::getJsonResponseData();

        static::assertCount(count($errors), $json->violations);

        foreach ($errors as $field => $message) {
            $violation = static::getViolation($field);
            static::assertNotNull($violation);
            static::assertEquals($message, $violation->message);
        }
    }

    public function provideInvalidProfileData()
    {
        yield [
            [
                'fullName' => '',
                'email' => 'lior@mail.com'
            ],
            [
                'fullName' => "Le nom complet est obligatoire !"
            ]
        ];
        yield [
            [
                'fullName' => '',
                'email' => '',
            ],
            [
                'fullName' => "Le nom complet est obligatoire !",
                'email' => "L'adresse email est obligatoire !"
            ]
        ];
        yield [
            [
                'email' => 'liormail.com',
                'fullName' => '',
            ],
            [
                'fullName' => "Le nom complet est obligatoire !",
                'email' => "L'adresse email soumise n'est pas au format réglementaire",
            ]
        ];
        yield [
            [
                'fullName' => '',
                'email' => 'jerome@mail.com',
            ],
            [
                'fullName' => "Le nom complet est obligatoire !",
                'email' => "Vous ne pouvez pas créer un compte avec cette adresse email",
            ]
        ];
    }
}
