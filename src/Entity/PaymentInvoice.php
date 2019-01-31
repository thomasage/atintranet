<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_payment_invoice")
 * @ORM\Entity(repositoryClass="App\Repository\PaymentInvoiceRepository")
 * @UniqueEntity(fields={"payment", "invoice"})
 */
class PaymentInvoice
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Payment
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment", inversedBy="paymentInvoices")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $payment;

    /**
     * @var Invoice|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice", inversedBy="paymentInvoices")
     *
     * @Assert\Valid()
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    private $amount;

    /**
     * PaymentInvoice constructor.
     */
    public function __construct()
    {
        $this->amount = '0.0';
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return PaymentInvoice
     */
    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     * @return PaymentInvoice
     */
    public function setPayment(Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return Invoice|null
     */
    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    /**
     * @param Invoice|null $invoice
     * @return PaymentInvoice
     */
    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }
}
