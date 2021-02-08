<?php

namespace App\Tests\Feature\Invoices;

use App\Tests\ApiTestCase;

class InvoicesValidationTest extends ApiTestCase
{
    /** 
     * @test 
     * @dataProvider provideInvalidInvoiceData
     */
    public function it_cant_create_an_invoice_if_data_is_invalid($data, $errors)
    {
        // Given there is a user authenticated
        static::actAsAuthenticated();

        // When we call /api/users in POST 
        static::jsonRequest('POST', '/api/invoices', $data);

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

    public function provideInvalidInvoiceData()
    {
        yield [
            [
                'description' => '',
            ],
            [
                'description' => "La description est obligatoire !"
            ]
        ];
    }
}
