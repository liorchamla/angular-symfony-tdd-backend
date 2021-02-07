<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 * @ApiResource(
 *  normalizationContext={"groups":{"invoiceRead"}},
 *  denormalizationContext={"groups":{"invoiceWrite"}}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("invoiceRead")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"invoiceRead", "invoiceWrite"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("invoiceRead")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=InvoiceLine::class, mappedBy="invoice", orphanRemoval=true, cascade={"PERSIST"})
     * @Groups({"invoiceRead", "invoiceWrite"})
     */
    private $lines;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->createdAt) {
            $this->createdAt = new DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @Groups("invoiceRead")
     */
    public function getAmount(): ?int
    {
        return array_reduce($this->lines->toArray(), fn (int $total, InvoiceLine $l) => $l->getAmount() + $total, 0);
    }

    /**
     * @return Collection|InvoiceLine[]
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(InvoiceLine $line): self
    {
        if (!$this->lines->contains($line)) {
            $this->lines[] = $line;
            $line->setInvoice($this);
        }

        return $this;
    }

    public function removeLine(InvoiceLine $line): self
    {
        if ($this->lines->removeElement($line)) {
            // set the owning side to null (unless already changed)
            if ($line->getInvoice() === $this) {
                $line->setInvoice(null);
            }
        }

        return $this;
    }
}
