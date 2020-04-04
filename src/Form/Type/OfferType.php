<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\Offer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OfferType extends AbstractRecordType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addClientField($builder);
        $this->addIssueDateField($builder);

        $builder->add(
            'validityDate',
            DateType::class,
            [
                'label' => 'field.validity_date',
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
        $builder->add(
            'details',
            CollectionType::class,
            [
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_type' => OfferDetailType::class,
                'label' => 'field.details',
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            static function (FormEvent $event): void {
                $offer = $event->getData();
                if (!$offer instanceof Offer) {
                    return;
                }
                $client = $offer->getClient();
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
                'data_class' => Offer::class,
            ]
        );
    }
}
