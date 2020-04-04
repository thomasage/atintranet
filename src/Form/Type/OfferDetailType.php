<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\OfferDetail;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferDetailType extends InvoiceDetailType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => OfferDetail::class,
            ]
        );
    }
}
