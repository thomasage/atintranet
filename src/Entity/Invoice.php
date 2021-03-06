<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_invoice",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="number_type_year", columns={"number", "type", "year"})
 *     })
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 * @UniqueEntity(fields={"type", "number", "year"}, message="notification.invoice_number_must_be_unique")
 */
class Invoice
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @var Client|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $client;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=3)
     */
    private $number;

    /**
     * @var string
     *
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
     * @var Collection|InvoiceDetail[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\InvoiceDetail",
     *     mappedBy="invoice",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"})
     */
    private $details;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $year;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length(min=1, max=255)
     */
    private $orderNumber;

    /**
     * @var Invoice|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Invoice")
     */
    private $credit;

    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->amountExcludingTax = '0.0';
        $this->amountIncludingTax = '0.0';
        $this->amountPaid = '0.0';
        $this->details = new ArrayCollection();
        $this->dueDate = new DateTime('+1 month');
        $this->issueDate = new DateTime();
        $this->taxRate = 0.2;
        $this->taxAmount = '0.0';
        $this->type = 'invoice';
        $this->year = (int) $this->issueDate->format('Y');
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

    public function getIssueDate(): DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(DateTimeInterface $issueDate): self
    {
        $this->issueDate = $issueDate;
        $this->year = (int) $issueDate->format('Y');

        return $this;
    }

    public function getDueDate(): DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(DateTimeInterface $dueDate): self
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

    /**
     * @return Collection|InvoiceDetail[]
     */
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

    public function getNumberComplete(): string
    {
        return sprintf('%s%s', $this->issueDate->format('ym'), $this->number);
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getCredit(): ?self
    {
        return $this->credit;
    }

    public function setCredit(?self $credit): self
    {
        $this->credit = $credit;

        return $this;
    }
}
