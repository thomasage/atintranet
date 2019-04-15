<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Repository\ClientRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => [
                        'invoice' => 'invoice',
                        'credit' => 'credit',
                    ],
                    'label' => 'field.type',
                    'required' => true,
                ]
            )
            ->add(
                'client',
                EntityType::class,
                [
                    'attr' => [
                        'data-url' => $this->router->generate('app_client_info'),
                    ],
                    'choice_value' => 'uuid',
                    'class' => Client::class,
                    'group_by' => function (Client $client): string {
                        return $this->translator->trans($client->getActive() ? 'active' : 'inactive');
                    },
                    'label' => 'field.client',
                    'query_builder' => function (ClientRepository $er): QueryBuilder {
                        return $er->createQueryBuilder('client')->addOrderBy('client.name', 'ASC');
                    },
                    'required' => true,
                ]
            )
            ->add(
                'issueDate',
                DateType::class,
                [
                    'label' => 'field.issue_date',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'dueDate',
                DateType::class,
                [
                    'label' => 'field.due_date',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'currency',
                CurrencyType::class,
                [
                    'label' => 'field.currency',
                    'preferred_choices' => ['EUR'],
                    'required' => true,
                ]
            )
            ->add(
                'comment',
                TextareaType::class,
                [
                    'label' => 'field.comment',
                    'required' => false,
                ]
            )
            ->add(
                'commentInternal',
                TextareaType::class,
                [
                    'label' => 'field.internal_comment',
                    'required' => false,
                ]
            )
            ->add(
                'address',
                AddressType::class,
                [
                    'label' => 'invoicing_address',
                    'required' => true,
                ]
            )
            ->add(
                'amountExcludingTax',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.amount_excluding_tax',
                ]
            )
            ->add(
                'taxRate',
                PercentType::class,
                [
                    'label' => 'field.tax_rate',
                    'required' => true,
                ]
            )
            ->add(
                'taxAmount',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.tax_amount',
                ]
            )
            ->add(
                'amountIncludingTax',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.amount_including_tax',
                ]
            )
            ->add(
                'amountPaid',
                MoneyType::class,
                [
                    'disabled' => true,
                    'label' => 'field.amount_paid',
                ]
            )
            ->add(
                'details',
                CollectionType::class,
                [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_type' => InvoiceDetailType::class,
                    'label' => 'field.details',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Invoice::class,
            ]
        );
    }
}
