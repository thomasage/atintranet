<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\ChoiceType\ClientChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class StatReportTimeType.
 */
class StatReportTimeType extends AbstractType
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
                    'required' => true,
                ]
            )
            ->add(
                'month',
                DateType::class,
                [
                    'label' => 'field.month',
                    'required' => true,
                    'widget' => 'choice',
                ]
            );
    }
}
