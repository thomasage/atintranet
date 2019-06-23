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
 * @ORM\Table(name="app_offer",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="number_year", columns={"number", "year"})
 *     })
 * @ORM\Entity(repositoryClass="App\Repository\OfferRepository")
 * @UniqueEntity(fields={"number", "year"}, message="notification.offer_number_must_be_unique")
 */
class Offer
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="offers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $issueDate;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $validityDate;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $number;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountExcludingTax = '0.0';

    /**
     * @ORM\Column(type="float"))
     */
    private $taxRate = 0.2;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $taxAmount = '0.0';

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amountIncludingTax = '0.0';

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
     * @var Collection|OfferDetail[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\OfferDetail",
     *     mappedBy="offer",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"})
     */
    private $details;

    /**
     * @ORM\Column(type="smallint")
     */
    private $year;

    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->details = new ArrayCollection();
        $this->issueDate = new DateTime();
        $this->validityDate = new DateTime('+1 months');
        $this->year = (int)$this->issueDate->format('Y');
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getIssueDate(): ?DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(DateTimeInterface $issueDate): self
    {
        $this->issueDate = $issueDate;
        $this->year = (int)$issueDate->format('Y');

        return $this;
    }

    public function getValidityDate(): ?DateTimeInterface
    {
        return $this->validityDate;
    }

    public function setValidityDate(DateTimeInterface $validityDate): self
    {
        $this->validityDate = $validityDate;

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

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection|OfferDetail[]
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(OfferDetail $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details[] = $detail;
            $detail->setOffer($this);
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
        $this->taxAmount = bcmul($this->amountExcludingTax, (string)$this->taxRate, 5);
        $this->amountIncludingTax = bcadd($this->amountExcludingTax, $this->taxAmount, 5);
    }

    public function removeDetail(OfferDetail $detail): self
    {
        if ($this->details->contains($detail)) {
            $this->details->removeElement($detail);
            // set the owning side to null (unless already changed)
            if ($detail->getOffer() === $this) {
                $detail->setOffer(null);
            }
        }

        $this->updateAmounts();

        return $this;
    }

    public function getNumberComplete(): string
    {
        return sprintf('%s%s', $this->issueDate->format('ym'), $this->number);
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

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $taxRate): self
    {
        $this->taxRate = $taxRate;

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

    public function getAmountIncludingTax(): string
    {
        return $this->amountIncludingTax;
    }

    public function setAmountIncludingTax(string $amountIncludingTax): self
    {
        $this->amountIncludingTax = $amountIncludingTax;

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

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }
}
