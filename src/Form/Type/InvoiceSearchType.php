<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\ChoiceType\ClientChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class InvoiceSearchType.
 */
class InvoiceSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
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
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => [
                        'invoice' => 'invoice',
                        'credit' => 'credit',
                    ],
                    'label' => 'field.type',
                    'required' => false,
                ]
            );
    }
}
