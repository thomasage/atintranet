<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class StatTurnoverSearchType.
 */
class StatTurnoverSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'start',
                DateType::class,
                [
                    'label' => 'field.start',
                    'required' => false,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'stop',
                DateType::class,
                [
                    'label' => 'field.stop',
                    'required' => false,
                    'widget' => 'single_text',
                ]
            );
    }
}
