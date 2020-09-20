<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientDeleteType.
 */
class ClientDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'confirmation',
            CheckboxType::class,
            [
                'label' => 'confirmation.delete_client',
                'mapped' => false,
                'required' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Client::class,
            ]
        );
    }
}
