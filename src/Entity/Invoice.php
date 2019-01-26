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
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=7)
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
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date")
     */
    private $issueDate;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date")
     */
    private $dueDate;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountExcludingTax;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $taxAmount;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountIncludingTax;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountPaid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=3)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=3)
     */
    private $currency;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=1)
     */
    private $comment;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=1)
     */
    private $commentInternal;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $closed;

    /**
     * @var bool
     *
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
     * Invoice constructor.
     * @throws \Exception
     */
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
        $this->taxAmount = '0.0';
        $this->type = 'invoice';
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return Invoice
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return Invoice
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getIssueDate(): \DateTimeInterface
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTimeInterface $issueDate
     * @return Invoice
     */
    public function setIssueDate(\DateTimeInterface $issueDate): self
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTimeInterface $dueDate
     * @return Invoice
     */
    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @return Address|null
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * @param Address $address
     * @return Invoice
     */
    public function setAddress(Address $address): self
    {
        $this->address = $address;

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
     * @return Invoice
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
     * @return Invoice
     */
    public function setAmountIncludingTax(string $amountIncludingTax): self
    {
        $this->amountIncludingTax = $amountIncludingTax;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmountPaid(): string
    {
        return $this->amountPaid;
    }

    /**
     * @param string $amountPaid
     * @return Invoice
     */
    public function setAmountPaid(string $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Invoice
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return Invoice
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommentInternal(): ?string
    {
        return $this->commentInternal;
    }

    /**
     * @param string|null $commentInternal
     * @return Invoice
     */
    public function setCommentInternal(?string $commentInternal): self
    {
        $this->commentInternal = $commentInternal;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClosed(): bool
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     * @return Invoice
     */
    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     * @return Invoice
     */
    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return Collection|InvoiceDetail[]
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    /**
     * @param InvoiceDetail $detail
     * @return Invoice
     */
    public function addDetail(InvoiceDetail $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details[] = $detail;
            $detail->setInvoice($this);
        }

        $this->updateAmounts();

        return $this;
    }

    private function updateAmounts(): void
    {
        $this->amountExcludingTax = '0';
        $this->taxAmount = '0';
        $this->amountIncludingTax = '0';

        foreach ($this->details as $detail) {
            $this->amountExcludingTax = bcadd($this->amountExcludingTax, $detail->getAmountExcludingTax(), 2);
            $this->taxAmount = bcadd($this->taxAmount, $detail->getTaxAmount(), 2);
            $this->amountIncludingTax = bcadd($this->amountIncludingTax, $detail->getAmountIncludingTax(), 2);
        }
    }

    /**
     * @param InvoiceDetail $detail
     * @return Invoice
     */
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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Invoice
     */
    public function setType(string $type): self
    {
        $this->type = $type;

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
     * @return Invoice
     */
    public function setTaxAmount(string $taxAmount): self
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }
}
