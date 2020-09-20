<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType.
 */
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'field.username',
                    'required' => true,
                ]
            )
            ->add(
                'enabled',
                ChoiceType::class,
                [
                    'choices' => [
                        'yes' => true,
                        'no' => false,
                    ],
                    'label' => 'field.enabled',
                    'required' => true,
                ]
            )
            ->add(
                'role',
                ChoiceType::class,
                [
                    'choices' => [
                        'ROLE_ADMIN' => 'ROLE_ADMIN',
                        'ROLE_CLIENT' => 'ROLE_CLIENT',
                    ],
                    'label' => 'field.role',
                    'required' => true,
                ]
            );

        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    /** @var User|null $data */
                    $data = $event->getData();
                    if (!$data) {
                        return;
                    }
                    $this->setupClientField($event->getForm(), $data->getRole());
                }
            );

        $builder
            ->get('role')
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $this->setupClientField($form->getParent(), $form->getData());
                }
            );
    }

    private function setupClientField(FormInterface $form, string $role): void
    {
        if ('ROLE_CLIENT' !== $role) {
            $form->remove('client');

            return;
        }

        $form->add(
            'client',
            EntityType::class,
            [
                'class' => Client::class,
                'label' => 'field.client',
                'query_builder' => function (ClientRepository $er): QueryBuilder {
                    return $er
                        ->createQueryBuilder('client')
                        ->addOrderBy('client.name', 'ASC');
                },
                'required' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
