<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice;
use App\Entity\OptionPaymentMethod;
use App\Entity\Payment;
use App\Entity\PaymentInvoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class PaymentFixtures
 * @package App\DataFixtures
 */
class PaymentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /** @var OptionPaymentMethod $method */
        $method = $this->getReference('payment_method_transfer');

        for ($i = 0; $i < 10; $i++) {

            $payment = new Payment();
            $payment
                ->setAmount((string)$faker->randomFloat(2, -10000, 10000))
                ->setMethod($method)
                ->setOperationDate($faker->dateTime())
                ->setThirdPartyName($faker->company);
            $manager->persist($payment);

            if ((float)$payment->getAmount() > 0.0 && $faker->boolean) {

                /** @var Invoice $invoice */
                $invoice = $this->getReference(sprintf('invoice-%d', $faker->numberBetween(0, 9)));

                $paymentInvoice = new PaymentInvoice();
                $paymentInvoice
                    ->setAmount($payment->getAmount())
                    ->setInvoice($invoice)
                    ->setPayment($payment);
                $manager->persist($paymentInvoice);

            }

        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class,
            OptionPaymentMethodFixtures::class,
        ];
    }
}
