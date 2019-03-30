<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Payment;
use App\Entity\PaymentInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentInvoiceType.
 */
class PaymentInvoiceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'invoice',
                InvoiceSelectorType::class,
                [
                    'label' => 'field.invoice',
                    'required' => true,
                ]
            );

        $builder
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                function (FormEvent $event) {
                    $currency = 'EUR';
                    /** @var PaymentInvoice|null $payment */
                    $paymentInvoice = $event->getData();
                    if ($paymentInvoice instanceof PaymentInvoice) {
                        $payment = $paymentInvoice->getPayment();
                        if ($payment instanceof Payment) {
                            $currency = $payment->getCurrency();
                        }
                    }
                    $event
                        ->getForm()
                        ->add(
                            'amount',
                            MoneyType::class,
                            [
                                'currency' => $currency,
                                'label' => 'field.amount',
                                'required' => true,
                            ]
                        );
                }
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => PaymentInvoice::class,
            ]
        );
    }
}
