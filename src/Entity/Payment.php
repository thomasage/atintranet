<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_payment")
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @var OptionPaymentMethod
     *
     * @ORM\ManyToOne(targetEntity="OptionPaymentMethod")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $method;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $operationDate;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\Date()
     */
    private $valueDate;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amount;

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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @var Collection|PaymentInvoice[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentInvoice",
     *     mappedBy="payment",
     *     cascade={"persist"},
     *     orphanRemoval=true)
     */
    private $paymentInvoices;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $thirdPartyName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Payment constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->amount = '0.0';
        $this->currency = 'EUR';
        $this->locked = false;
        $this->operationDate = new \DateTime();
        $this->paymentInvoices = new ArrayCollection();
    }

    /**
     * @return Collection|PaymentInvoice[]
     */
    public function getPaymentInvoices(): Collection
    {
        return $this->paymentInvoices;
    }

    /**
     * @param PaymentInvoice $paymentInvoice
     *
     * @return Payment
     */
    public function addPaymentInvoice(PaymentInvoice $paymentInvoice): self
    {
        if (!$this->paymentInvoices->contains($paymentInvoice)) {
            $this->paymentInvoices[] = $paymentInvoice;
            $paymentInvoice->setPayment($this);
        }

        return $this;
    }

    /**
     * @param PaymentInvoice $paymentInvoice
     *
     * @return Payment
     */
    public function removePaymentInvoice(PaymentInvoice $paymentInvoice): self
    {
        if ($this->paymentInvoices->contains($paymentInvoice)) {
            $this->paymentInvoices->removeElement($paymentInvoice);
            // set the owning side to null (unless already changed)
            if ($paymentInvoice->getPayment() === $this) {
                $paymentInvoice->setPayment(null);
            }
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getOperationDate(): \DateTimeInterface
    {
        return $this->operationDate;
    }

    /**
     * @param \DateTimeInterface $operationDate
     *
     * @return Payment
     */
    public function setOperationDate(\DateTimeInterface $operationDate): self
    {
        $this->operationDate = $operationDate;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getValueDate(): ?\DateTimeInterface
    {
        return $this->valueDate;
    }

    /**
     * @param \DateTimeInterface|null $valueDate
     *
     * @return Payment
     */
    public function setValueDate(?\DateTimeInterface $valueDate): self
    {
        $this->valueDate = $valueDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     *
     * @return Payment
     */
    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

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
     *
     * @return Payment
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

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
     *
     * @return Payment
     */
    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return OptionPaymentMethod|null
     */
    public function getMethod(): ?OptionPaymentMethod
    {
        return $this->method;
    }

    /**
     * @param OptionPaymentMethod $method
     *
     * @return Payment
     */
    public function setMethod(OptionPaymentMethod $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    /**
     * @param string|null $bankName
     *
     * @return Payment
     */
    public function setBankName(?string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCssClass(): string
    {
        if ((float) $this->amount < 0.0) {
            return 'text-danger';
        }

        return '';
    }

    /**
     * @return string|null
     */
    public function getThirdPartyName(): ?string
    {
        return $this->thirdPartyName;
    }

    /**
     * @param string $thirdPartyName
     *
     * @return Payment
     */
    public function setThirdPartyName(string $thirdPartyName): self
    {
        $this->thirdPartyName = $thirdPartyName;

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
     *
     * @return Payment
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
