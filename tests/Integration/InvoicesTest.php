<?php

namespace App\Tests\Integration;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InvoicesTest extends KernelTestCase
{
    /** @test */
    public function it_cant_create_an_invoice_without_a_user()
    {
        static::bootKernel();

        $em = static::$container->get(EntityManagerInterface::class);

        $invoice = (new Invoice)
            ->setDescription('MOCK_DESCRIPTION');

        $this->expectException(\Doctrine\DBAL\Exception::class);

        $em->persist($invoice);
        $em->flush();
    }

    /** @test */
    public function it_has_an_amount_of_0_if_no_lines()
    {
        $invoice = (new Invoice)
            ->setDescription('MOCK_DESCRIPTION');

        static::assertSame(0, $invoice->getAmount());
    }

    /** @test */
    public function it_calculates_amount_with_lines_amount()
    {
        static::bootKernel();

        $em = static::$container->get(EntityManagerInterface::class);

        $invoice = (new Invoice)
            ->setDescription('MOCK_DESCRIPTION');

        $line = (new InvoiceLine)
            ->setAmount(10)
            ->setDescription('MOCK_LINE_DESCRIPTION_1');


        $line2 = (new InvoiceLine)
            ->setAmount(20)
            ->setDescription('MOCK_LINE_DESCRIPTION_2');

        // It should work in both way : invoice->line / line->invoice
        $invoice->addLine($line);
        $line2->setInvoice($invoice);

        static::assertEquals(30, $invoice->getAmount());
    }
}
