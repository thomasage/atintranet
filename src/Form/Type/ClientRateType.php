<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\ClientRate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ClientRate|null $rate */
        $rate = $options['data'];

        $builder
            ->add(
                'client',
                TextType::class,
                [
                    'data' => $rate instanceof ClientRate ? (string)$rate->getClient() : '',
                    'disabled' => true,
                    'label' => 'field.client',
                    'mapped' => false,
                ]
            )
            ->add(
                'startedAt',
                DateType::class,
                [
                    'label' => 'field.start',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'hourlyRateOnSite',
                MoneyType::class,
                [
                    'label' => 'field.on_site',
                    'required' => true,
                ]
            )
            ->add(
                'hourlyRateOffSite',
                MoneyType::class,
                [
                    'label' => 'field.off_site',
                    'required' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ClientRate::class,
            ]
        );
    }
}
