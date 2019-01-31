<?php
declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\PaymentInvoice;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentInvoiceListenerTest
 * @package App\Tests\EventListener
 */
class PaymentInvoiceListenerTest extends TestCase
{
    public function testPaymentUpdateInvoiceAmountPaid(): void
    {
        $faker = Factory::create('fr_FR');

        $invoice = new Invoice();
        self::assertEquals(0, $invoice->getAmountPaid());

        $payment = new Payment();

        $paymentInvoice = new PaymentInvoice();
        $paymentInvoice
            ->setAmount((string)$faker->randomFloat(2, 100, 1000))
            ->setInvoice($invoice)
            ->setPayment($payment);

        self::assertSame($paymentInvoice->getAmount(), $invoice->getAmountPaid());

    }
}
