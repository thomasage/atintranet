<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractRecordType extends AbstractType
{
    protected $router;

    protected $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    protected function addClientField(FormBuilderInterface $builder): void
    {
        $builder->add(
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
                    return $er->createQueryBuilder('client')
                        ->addOrderBy('client.active', 'DESC')
                        ->addOrderBy('client.name', 'ASC');
                },
                'required' => true,
            ]
        );
    }

    protected function addIssueDateField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'issueDate',
            DateType::class,
            [
                'label' => 'field.issue_date',
                'required' => true,
                'widget' => 'single_text',
            ]
        );
    }

    protected function addCommentField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'comment',
            TextareaType::class,
            [
                'attr' => [
                    'class' => 'external_comment',
                    'placeholder' => 'field.comment',
                ],
                'label' => 'field.comment',
                'required' => false,
            ]
        );
    }

    protected function addCommentInternalField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'commentInternal',
            TextareaType::class,
            [
                'attr' => [
                    'class' => 'internal_comment',
                    'placeholder' => 'field.internal_comment',
                ],
                'label' => 'field.internal_comment',
                'required' => false,
            ]
        );
    }

    protected function addAddressField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'address',
            AddressType::class,
            [
                'label' => 'invoicing_address',
                'required' => true,
            ]
        );
    }

    protected function addAmountExcludingTaxField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'amountExcludingTax',
            MoneyType::class,
            [
                'disabled' => true,
                'label' => 'field.amount_excluding_tax',
            ]
        );
    }

    protected function addTaxRateField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'taxRate',
            PercentType::class,
            [
                'label' => 'field.tax_rate',
                'required' => true,
            ]
        );
    }

    protected function addTaxAmountField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'taxAmount',
            MoneyType::class,
            [
                'disabled' => true,
                'label' => 'field.tax_amount',
            ]
        );
    }

    protected function addAmountIncludingTaxField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'amountIncludingTax',
            MoneyType::class,
            [
                'disabled' => true,
                'label' => 'field.amount_including_tax',
            ]
        );
    }
}
