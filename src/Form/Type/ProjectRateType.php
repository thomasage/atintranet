<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\ProjectRate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProjectRateType.
 */
class ProjectRateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ProjectRate|null $rate */
        $rate = $options['data'];

        $builder
            ->add(
                'project',
                TextType::class,
                [
                    'data' => $rate instanceof ProjectRate ? (string) $rate->getProject() : '',
                    'disabled' => true,
                    'label' => 'field.project',
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

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProjectRate::class,
            ]
        );
    }
}
