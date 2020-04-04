<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_invoice_detail")
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceDetailRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class InvoiceDetail implements RecordDetailInterface
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice", inversedBy="details")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $invoice;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1)
     */
    private $designation;

    /**
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     *
     * @Assert\NotBlank()
     */
    private $amountUnit;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     *
     * @Assert\NotBlank()
     */
    private $amountTotal;

    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->amountUnit = '0.0';
        $this->amountTotal = '0.0';
        $this->quantity = 1.0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;
        $this->updateAmounts();

        return $this;
    }

    private function updateAmounts(): void
    {
        $this->amountTotal = bcmul($this->amountUnit, (string)$this->quantity, 5);
        if ($this->invoice instanceof Invoice) {
            $this->invoice->updateAmounts();
        }
    }

    public function getAmountUnit(): string
    {
        return $this->amountUnit;
    }

    public function setAmountUnit(string $amountUnit): self
    {
        $this->amountUnit = $amountUnit;
        $this->updateAmounts();

        return $this;
    }

    public function getAmountTotal(): string
    {
        return $this->amountTotal;
    }

    public function setAmountTotal(string $amountTotal): self
    {
        $this->amountTotal = $amountTotal;

        return $this;
    }
}
