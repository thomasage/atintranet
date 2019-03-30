<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\ChoiceType\PaymentMethodChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PaymentSearchType.
 */
class PaymentSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'third_party',
                TextType::class,
                [
                    'label' => 'field.third_party',
                    'required' => false,
                ]
            )
            ->add(
                'payment_method',
                PaymentMethodChoiceType::class,
                [
                    'label' => 'field.payment_method',
                    'required' => false,
                ]
            );
    }
}
