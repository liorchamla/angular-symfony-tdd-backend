<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceLineRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=InvoiceLineRepository::class)
 * @ApiResource(
 *  normalizationContext={"groups":{"invoiceRead"}}
 * )
 */
class InvoiceLine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("invoiceRead")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"invoiceRead", "invoiceWrite"})
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"invoiceRead", "invoiceWrite"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Invoice::class, inversedBy="lines")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Groups({"invoiceWrite"})
     */
    private $invoice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        if ($invoice && !$invoice->getLines()->contains($this)) {
            $invoice->getLines()->add($this);
        }

        return $this;
    }
}
