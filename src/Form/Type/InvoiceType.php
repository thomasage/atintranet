<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Repository\ClientRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class InvoiceType.
 */
class InvoiceType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * InvoiceType constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'number',
                TextType::class,
                [
                    'disabled' => true,
                    'label' => 'field.number',
                ]
            )
            ->add(
                'client',
                EntityType::class,
                [
                    'attr' => [
                        'data-url' => $this->router->generate('app_client_info'),
                    ],
                    'class' => Client::class,
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

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Invoice::class,
            ]
        );
    }
}
