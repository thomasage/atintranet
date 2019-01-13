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
class InvoiceDetail
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @var Invoice
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice", inversedBy="details")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1)
     */
    private $designation;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     *
     * @Assert\NotBlank()
     */
    private $amountUnit;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     *
     * @Assert\NotBlank()
     */
    private $amountExcludingTax;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     */
    private $taxRate;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     *
     * @Assert\NotBlank()
     */
    private $taxAmount;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     *
     * @Assert\NotBlank()
     */
    private $amountIncludingTax;

    /**
     * InvoiceDetail constructor.
     */
    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->amountExcludingTax = '0.0';
        $this->amountIncludingTax = '0.0';
        $this->amountUnit = '0.0';
        $this->quantity = 1.0;
        $this->taxRate = 0.2;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Invoice|null
     */
    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     * @return InvoiceDetail
     */
    public function setInvoice(Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * @param string $designation
     * @return InvoiceDetail
     */
    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     * @return InvoiceDetail
     */
    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;
        $this->updateAmounts();

        return $this;
    }

    private function updateAmounts(): void
    {
        $this->amountExcludingTax = bcmul($this->amountUnit, (string)$this->quantity, 2);
        $this->taxAmount = bcmul($this->amountExcludingTax, (string)$this->taxRate, 2);
        $this->amountIncludingTax = bcadd($this->amountExcludingTax, $this->taxAmount, 2);
    }

    /**
     * @return string
     */
    public function getAmountUnit(): string
    {
        return $this->amountUnit;
    }

    /**
     * @param string $amountUnit
     * @return InvoiceDetail
     */
    public function setAmountUnit(string $amountUnit): self
    {
        $this->amountUnit = $amountUnit;
        $this->updateAmounts();

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     * @return InvoiceDetail
     */
    public function setTaxRate(float $taxRate): self
    {
        $this->taxRate = $taxRate;
        $this->updateAmounts();

        return $this;
    }

    /**
     * @return string
     */
    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }

    /**
     * @param string $taxAmount
     * @return InvoiceDetail
     */
    public function setTaxAmount(string $taxAmount): self
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmountExcludingTax(): string
    {
        return $this->amountExcludingTax;
    }

    /**
     * @param string $amountExcludingTax
     * @return InvoiceDetail
     */
    public function setAmountExcludingTax(string $amountExcludingTax): self
    {
        $this->amountExcludingTax = $amountExcludingTax;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmountIncludingTax(): string
    {
        return $this->amountIncludingTax;
    }

    /**
     * @param string $amountIncludingTax
     * @return InvoiceDetail
     */
    public function setAmountIncludingTax(string $amountIncludingTax): self
    {
        $this->amountIncludingTax = $amountIncludingTax;

        return $this;
    }
}
