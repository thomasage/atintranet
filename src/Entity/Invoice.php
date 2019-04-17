<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_invoice")
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 * @UniqueEntity(fields={"number", "type"})
 */
class Invoice
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"invoice", "credit"})
     */
    private $type;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank()
     */
    private $issueDate;

    /**
     * @ORM\Column(type="date")
     */
    private $dueDate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $address;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountExcludingTax;

    /**
     * @ORM\Column(type="float"))
     */
    private $taxRate;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $taxAmount;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountIncludingTax;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountPaid;

    /**
     * @ORM\Column(type="string", length=3)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=3)
     */
    private $currency;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=1)
     */
    private $comment;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=1)
     */
    private $commentInternal;

    /**
     * @ORM\Column(type="boolean")
     */
    private $closed;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @var Collection|InvoiceDetail[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\InvoiceDetail",
     *     mappedBy="invoice",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"})
     */
    private $details;

    /**
     * @var Collection|PaymentInvoice[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentInvoice",
     *     mappedBy="invoice",
     *     orphanRemoval=true)
     */
    private $paymentInvoices;

    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->amountExcludingTax = '0.0';
        $this->amountIncludingTax = '0.0';
        $this->amountPaid = '0.0';
        $this->closed = false;
        $this->currency = 'EUR';
        $this->details = new ArrayCollection();
        $this->dueDate = new \DateTime('+1 month');
        $this->issueDate = new \DateTime();
        $this->locked = false;
        $this->paymentInvoices = new ArrayCollection();
        $this->taxRate = 0.2;
        $this->taxAmount = '0.0';
        $this->type = 'invoice';
    }

    public function __toString(): string
    {
        return (string) $this->number;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getIssueDate(): \DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(\DateTimeInterface $issueDate): self
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAmountExcludingTax(): string
    {
        return $this->amountExcludingTax;
    }

    public function setAmountExcludingTax(string $amountExcludingTax): self
    {
        $this->amountExcludingTax = $amountExcludingTax;

        return $this;
    }

    public function getAmountIncludingTax(): string
    {
        return $this->amountIncludingTax;
    }

    public function setAmountIncludingTax(string $amountIncludingTax): self
    {
        $this->amountIncludingTax = $amountIncludingTax;

        return $this;
    }

    public function getAmountPaid(): string
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(string $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCommentInternal(): ?string
    {
        return $this->commentInternal;
    }

    public function setCommentInternal(?string $commentInternal): self
    {
        $this->commentInternal = $commentInternal;

        return $this;
    }

    public function getClosed(): bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(InvoiceDetail $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details[] = $detail;
            $detail->setInvoice($this);
        }

        $this->updateAmounts();

        return $this;
    }

    public function updateAmounts(): void
    {
        $this->amountExcludingTax = '0';
        foreach ($this->details as $detail) {
            $this->amountExcludingTax = bcadd($this->amountExcludingTax, $detail->getAmountTotal(), 5);
        }
        $this->taxAmount = bcmul($this->amountExcludingTax, (string) $this->taxRate, 5);
        $this->amountIncludingTax = bcadd($this->amountExcludingTax, $this->taxAmount, 5);
    }

    public function removeDetail(InvoiceDetail $detail): self
    {
        if ($this->details->contains($detail)) {
            $this->details->removeElement($detail);
            // set the owning side to null (unless already changed)
            if ($detail->getInvoice() === $this) {
                $detail->setInvoice(null);
            }
        }

        $this->updateAmounts();

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): self
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $taxRate): self
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * @return Collection|PaymentInvoice[]
     */
    public function getPaymentInvoices(): Collection
    {
        return $this->paymentInvoices;
    }

    public function addPaymentInvoice(PaymentInvoice $paymentInvoice): self
    {
        if (!$this->paymentInvoices->contains($paymentInvoice)) {
            $this->paymentInvoices[] = $paymentInvoice;
            $paymentInvoice->setInvoice($this);
        }

        return $this;
    }

    public function removePaymentInvoice(PaymentInvoice $paymentInvoice): self
    {
        if ($this->paymentInvoices->contains($paymentInvoice)) {
            $this->paymentInvoices->removeElement($paymentInvoice);
            // set the owning side to null (unless already changed)
            if ($paymentInvoice->getInvoice() === $this) {
                $paymentInvoice->setInvoice(null);
            }
        }

        return $this;
    }

    public function getNumberComplete(): string
    {
        return sprintf('%s%s', $this->issueDate->format('ym'), $this->number);
    }
}
