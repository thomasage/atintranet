<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\InvoiceDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceDetailType
 * @package App\Form\Type
 */
class InvoiceDetailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'designation',
                TextareaType::class,
                [
                    'label' => 'field.designation',
                    'required' => true,
                ]
            )
            ->add(
                'quantity',
                NumberType::class,
                [
                    'label' => 'field.quantity',
                    'required' => true,
                ]
            )
            ->add(
                'amountUnit',
                MoneyType::class,
                [
                    'label' => 'field.amount_unit',
                    'required' => true,
                ]
            )
            ->add(
                'taxRate',
                PercentType::class,
                [
                    'label' => 'field.tax_rate',
                    'required' => true,
                ]
            )
            ->add(
                'amountIncludingTax',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.amount_including_tax',
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => InvoiceDetail::class,
            ]
        );
    }
}
