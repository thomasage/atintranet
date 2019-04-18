<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientType.
 */
class ClientType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'field.name',
                    'required' => true,
                ]
            )
            ->add(
                'code',
                TextType::class,
                [
                    'label' => 'field.code',
                    'required' => true,
                ]
            )
            ->add(
                'active',
                ChoiceType::class,
                [
                    'choices' => [
                        'yes' => 1,
                        'no' => 0,
                    ],
                    'label' => 'field.active',
                    'required' => true,
                ]
            )
            ->add(
                'comment',
                TextareaType::class,
                [
                    'label' => 'field.comment',
                    'required' => false,
                ]
            )
            ->add(
                'supplierNumber',
                TextType::class,
                [
                    'label' => 'field.supplier_number',
                    'required' => false,
                ]
            )
            ->add(
                'vatNumber',
                TextType::class,
                [
                    'label' => 'field.vat_number',
                    'required' => false,
                ]
            )
            ->add(
                'addressPrimary',
                AddressType::class,
                [
                    'label' => 'primary_address',
                    'required' => true,
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
                'data_class' => Client::class,
            ]
        );
    }
}
