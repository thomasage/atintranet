<?php
declare(strict_types=1);

namespace App\Form\Type;

use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class UserAddType
 * @package App\Form\Type
 */
class UserAddType extends UserType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'password',
                RepeatedType::class,
                [
                    'constraints' => [
                        new Length(['min' => 8]),
                        new NotBlank(),
                        new PasswordStrength(['minLength' => 8, 'minStrength' => 3]),
                    ],
                    'first_options' => [
                        'label' => 'field.password',
                    ],
                    'mapped' => false,
                    'required' => true,
                    'second_options' => [
                        'label' => 'field.confirmation',
                    ],
                    'type' => PasswordType::class,
                ]
            );
    }
}
