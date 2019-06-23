<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\ChoiceType\ClientChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OfferSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'client',
                ClientChoiceType::class,
                [
                    'label' => 'field.client',
                    'required' => false,
                ]
            );
    }
}
