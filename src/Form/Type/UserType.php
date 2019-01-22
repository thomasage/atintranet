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
 * Class UserType
 * @package App\Form\Type
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User|null $user */
        $user = $options['data'] ?? null;

        $roles = $user ? $user->getRoles() : [];

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
                'roles',
                ChoiceType::class,
                [
                    'choices' => [
                        'ROLE_ADMIN' => 'ROLE_ADMIN',
                        'ROLE_CLIENT' => 'ROLE_CLIENT',
                    ],
                    'label' => 'field.roles',
                    'multiple' => true,
                    'required' => false,
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
                    $this->setupClientField($event->getForm(), $data->getRoles());
                }
            );

        $builder
            ->get('roles')
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $this->setupClientField($form->getParent(), $form->getData());
                }
            );
    }

    /**
     * @param FormInterface $form
     * @param array $roles
     */
    private function setupClientField(FormInterface $form, array $roles): void
    {
        if (!in_array('ROLE_CLIENT', $roles, true)) {
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

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}