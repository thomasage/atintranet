<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType.
 */
class AddressType extends AbstractType
{
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
                'address',
                TextareaType::class,
                [
                    'label' => 'field.address',
                    'required' => false,
                ]
            )
            ->add(
                'postcode',
                TextType::class,
                [
                    'label' => 'field.postcode',
                    'required' => true,
                ]
            )
            ->add(
                'city',
                TextType::class,
                [
                    'label' => 'field.city',
                    'required' => true,
                ]
            )
            ->add(
                'country',
                CountryType::class,
                [
                    'label' => 'field.country',
                    'preferred_choices' => ['FR'],
                    'required' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Address::class,
            ]
        );
    }
}
