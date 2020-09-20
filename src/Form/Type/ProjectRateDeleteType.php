<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\ProjectRate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProjectRateDeleteType.
 */
class ProjectRateDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'project',
                TextType::class,
                [
                    'disabled' => true,
                    'label' => 'field.project',
                ]
            )
            ->add(
                'startedAt',
                DateType::class,
                [
                    'disabled' => true,
                    'label' => 'field.start',
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'hourlyRateOnSite',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.on_site',
                ]
            )
            ->add(
                'hourlyRateOffSite',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.off_site',
                ]
            )
            ->add(
                'confirmation',
                CheckboxType::class,
                [
                    'label' => 'confirmation.delete_project_rate',
                    'mapped' => false,
                    'required' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProjectRate::class,
            ]
        );
    }
}
