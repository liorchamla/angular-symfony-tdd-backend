<?php

namespace App\Tests\Feature\Invoices;

use App\Tests\ApiTestCase;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;

class InvoicesSecurityTest extends ApiTestCase
{
    /** @test */
    public function it_cant_display_data_for_an_invoice_as_an_anonymous_user()
    {
        // Given there is an invoice in the database 
        $invoice = static::getRandomInvoice();

        // When we call /api/invoices/{id}
        static::$client->request('GET', '/api/invoices/' . $invoice->getId());

        // Then we should receive JSON response
        static::assertResponseStatusCodeSame(401);
    }

    /** @test */
    public function it_cant_display_all_invoices_as_an_anonymous_user()
    {
        // Given there are invoices in the database 
        // When we call /api/invoices/{id}
        static::$client->request('GET', '/api/invoices');

        // Then we should receive a 401
        static::assertResponseStatusCodeSame(401);
    }

    /** @test */
    public function it_cant_create_a_new_invoice_as_an_anonymous_user()
    {
        // Given we have a new invoice
        $data = [
            'description' => 'MOCK_INVOICE_DESCRIPTION',
            'lines' => [
                ['description' => 'MOCK_LINE_1', 'amount' => 10],
                ['description' => 'MOCK_LINE_2', 'amount' => 20],
                ['description' => 'MOCK_LINE_3', 'amount' => 30]
            ]
        ];

        // When we call /api/invoices with a POST request 
        static::jsonRequest('POST', '/api/invoices', $data);

        // Then the response should be 401
        static::assertResponseStatusCodeSame(401);

        // And the new invoice should not be in the database with a setted createdAt
        $invoice = self::$container->get(InvoiceRepository::class)->findOneBy(['description' => 'MOCK_INVOICE_DESCRIPTION']);

        static::assertNull($invoice);
    }

    /** @test */
    public function it_cant_edit_an_invoice_as_an_anonymous_user()
    {
        // Given we have an invoice in the database
        $invoice = static::getRandomInvoice();

        // And we have modified data
        $updatedData = [
            'description' => 'MOCK_UPDATED_DATA',
            'lines' => [
                ['description' => 'MOCK_UPDATED_LINE1', 'amount' => 20],
                ['description' => 'MOCK_UPDATED_LINE2', 'amount' => 30],
            ]
        ];

        // When we call /api/invoices/{id} with PUT method
        static::jsonRequest('PUT', '/api/invoices/' . $invoice->getId(), $updatedData);

        // Then the response should be successful and JSON
        static::assertResponseStatusCodeSame(401);


        // And invoice should not have been updated in database
        $updatedInvoice = static::$container->get(InvoiceRepository::class)->findOneBy([
            'description' => 'MOCK_UPDATED_DATA'
        ]);
        static::assertNull($updatedInvoice);
    }

    /** @test */
    public function it_cant_edit_an_invoice_as_authenticated_user_but_not_owner()
    {
        // Given we have 2 users in the database (Jerome and Anne)
        $jerome = static::$container->get(UserRepository::class)->findOneBy(['email' => 'jerome@mail.com']);
        $anne = static::$container->get(UserRepository::class)->findOneBy(['email' => 'anne@mail.com']);

        // And we are authenticated with Jerome
        static::actAsAuthenticated($jerome);

        // And we have an invoice in the database for Anne
        $invoice = $anne->getRandomInvoice();

        // And we have modified data
        $updatedData = [
            'description' => 'MOCK_UPDATED_DATA',
            'lines' => [
                ['description' => 'MOCK_UPDATED_LINE1', 'amount' => 20],
                ['description' => 'MOCK_UPDATED_LINE2', 'amount' => 30],
            ]
        ];

        // When we call /api/invoices/{id} with PUT method
        static::jsonRequest('PUT', '/api/invoices/' . $invoice->getId(), $updatedData);

        // Then the response should be Not found (401)
        static::assertResponseStatusCodeSame(404);


        // And invoice should not have been updated in database
        $updatedInvoice = static::$container->get(InvoiceRepository::class)->findOneBy([
            'description' => 'MOCK_UPDATED_DATA'
        ]);
        static::assertNull($updatedInvoice);
    }

    /** @test */
    public function it_cant_delete_an_invoice_as_an_anonymous_user()
    {
        // Given we have an invoice in the database
        $invoice = static::getRandomInvoice();

        $invoiceId = $invoice->getId();

        // When we call /api/invoices/{id} with DELETE method
        static::jsonRequest('DELETE', '/api/invoices/' . $invoiceId);

        // Then the response should have 401
        static::assertResponseStatusCodeSame(401);

        // And the invoice should not be found anymore in the database
        static::assertNotNull(
            static::$container->get(InvoiceRepository::class)->find($invoiceId)
        );
    }

    /** @test */
    public function it_cant_delete_an_invoice_as_authenticated_user_but_not_owner()
    {
        // Given we have 2 users in the database (Jerome and Anne)
        $jerome = static::$container->get(UserRepository::class)->findOneBy(['email' => 'jerome@mail.com']);
        $anne = static::$container->get(UserRepository::class)->findOneBy(['email' => 'anne@mail.com']);

        // And we are authenticated with Jerome
        static::actAsAuthenticated($jerome);

        // And we have an invoice in the database for Anne
        $invoice = $anne->getRandomInvoice();

        $invoiceId = $invoice->getId();

        // When we call /api/invoices/{id} with DELETE method
        static::jsonRequest('DELETE', '/api/invoices/' . $invoiceId);

        // Then the response should have 404 status
        static::assertResponseStatusCodeSame(404);

        // And the invoice should not be found anymore in the database
        static::assertNotNull(
            static::$container->get(InvoiceRepository::class)->find($invoiceId)
        );
    }
}
