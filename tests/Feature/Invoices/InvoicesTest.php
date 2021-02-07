<?php

namespace App\Tests\Feature\Invoices;

use App\Repository\InvoiceRepository;
use App\Tests\ApiTestCase;
use DateTimeInterface;

class InvoicesTest extends ApiTestCase
{

    /** @test */
    public function it_displays_json_data_for_an_invoice()
    {
        // Given there is an invoice in the database 
        $invoice = self::$container->get(InvoiceRepository::class)->findOneBy([]);

        // When we call /api/invoices/{id}
        static::$client->request('GET', '/api/invoices/' . $invoice->getId());

        // Then we should receive JSON response
        static::assertResponseIsSuccessful();
        static::assertJsonResponse();

        // And we should see the Invoice data
        $data = static::getJsonResponseData();
        static::assertEquals($invoice->getDescription(), $data->description);
        static::assertEquals($invoice->getAmount(), $data->amount);
        static::assertEquals($invoice->getId(), $data->id);
        static::assertEquals($invoice->getCreatedAt()->format(DateTimeInterface::RFC3339), $data->createdAt);
    }

    /** @test */
    public function it_displays_json_data_for_all_invoices()
    {
        // Given there are invoices in the database 
        $invoices = self::$container->get(InvoiceRepository::class)->findAll();

        // When we call /api/invoices/{id}
        static::$client->request('GET', '/api/invoices');

        // Then we should receive JSON response
        static::assertResponseIsSuccessful();
        static::assertJsonResponse();

        // And we should see the Invoice data
        $data = static::getJsonResponseData();

        foreach ($invoices as $index => $invoice) {
            static::assertEquals($invoice->getDescription(), $data[$index]->description);
            static::assertEquals($invoice->getAmount(), $data[$index]->amount);
            static::assertEquals($invoice->getId(), $data[$index]->id);
            static::assertEquals($invoice->getCreatedAt()->format(DateTimeInterface::RFC3339), $data[$index]->createdAt);
        }
    }

    /** @test */
    public function it_can_create_a_new_invoice()
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

        // Then we receive confirmation and JSON data
        static::assertResponseStatusCodeSame(201);
        static::assertJsonResponse();

        // And we should see the Invoice data
        $json = static::getJsonResponseData();
        static::assertEquals($data['description'], $json->description);
        static::assertEquals(60, $json->amount);
        static::assertNotNull($json->id);
        static::assertNotNull($json->createdAt);
        // And lines should now be available too
        static::assertCount(3, $json->lines);

        foreach ($data['lines'] as $index => $line) {
            static::assertEquals($line['description'], $json->lines[$index]->description);
            static::assertEquals($line['amount'], $json->lines[$index]->amount);
        }

        // And the new invoice should be in the database with a setted createdAt
        $invoice = self::$container->get(InvoiceRepository::class)->findOneBy(['description' => 'MOCK_INVOICE_DESCRIPTION']);

        static::assertNotNull($invoice);
    }

    /** @test */
    public function it_can_edit_an_invoice()
    {
        // Given we have an invoice in the database
        $invoice = static::$container->get(InvoiceRepository::class)->findOneBy([]);

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
        static::assertResponseStatusCodeSame(200);
        static::assertJsonResponse();

        // And the response should contain new data
        $json = static::getJsonResponseData();
        static::assertEquals($updatedData['description'], $json->description);

        // And invoice should be updated in database
        $updatedInvoice = static::$container->get(InvoiceRepository::class)->findOneBy([
            'description' => 'MOCK_UPDATED_DATA'
        ]);
        static::assertNotNull($updatedInvoice);
        static::assertEquals(50, $updatedInvoice->getAmount());
    }

    /** @test */
    public function it_can_delete_an_invoice()
    {
        // Given we have an invoice in the database
        $invoice = static::$container->get(InvoiceRepository::class)->findOneBy([]);

        $invoiceId = $invoice->getId();

        // When we call /api/invoices/{id} with DELETE method
        static::jsonRequest('DELETE', '/api/invoices/' . $invoiceId);

        // Then the response should have 204 status
        static::assertResponseStatusCodeSame(204);

        // And the invoice should not be found anymore in the database
        static::assertNull(
            static::$container->get(InvoiceRepository::class)->find($invoiceId)
        );
    }
}
