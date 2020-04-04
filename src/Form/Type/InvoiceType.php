<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\Invoice;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class InvoiceType extends AbstractRecordType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => [
                        'invoice' => 'invoice',
                        'credit' => 'credit',
                    ],
                    'label' => 'field.type',
                    'required' => true,
                ]
            );
        $this->addClientField($builder);
        $this->addIssueDateField($builder);
        $builder->add(
                'dueDate',
                DateType::class,
                [
                    'label' => 'field.due_date',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            );
        $this->addCommentField($builder);
        $this->addCommentInternalField($builder);
        $this->addAddressField($builder);
        $this->addAmountExcludingTaxField($builder);
        $this->addTaxRateField($builder);
        $this->addTaxAmountField($builder);
        $this->addAmountIncludingTaxField($builder);
        $builder
            ->add(
                'amountPaid',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.amount_paid',
                ]
            )
            ->add(
                'details',
                CollectionType::class,
                [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_type' => InvoiceDetailType::class,
                    'label' => 'field.details',
                ]
            )
            ->add(
                'orderNumber',
                TextType::class,
                [
                    'label' => 'field.order_number',
                    'required' => false,
                ]
            );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            static function (FormEvent $event): void {
                $invoice = $event->getData();
                if (!$invoice instanceof Invoice) {
                    return;
                }
                $client = $invoice->getClient();
                if (!$client instanceof Client || '' === (string) $client->getSupplierNumber()) {
                    return;
                }
                $event->getForm()
                    ->add(
                        'supplierNumber',
                        TextType::class,
                        [
                            'data' => $client->getSupplierNumber(),
                            'disabled' => true,
                            'label' => 'field.supplier_number',
                            'mapped' => false,
                        ]
                    );
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Invoice::class,
            ]
        );
    }
}
