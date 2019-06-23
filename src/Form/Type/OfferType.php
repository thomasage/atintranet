<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\Offer;
use App\Repository\ClientRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OfferType extends AbstractType
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
                'validityDate',
                DateType::class,
                [
                    'label' => 'field.validity_date',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            )
            ->add(
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
            )
            ->add(
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
                'details',
                CollectionType::class,
                [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_type' => OfferDetailType::class,
                    'label' => 'field.details',
                ]
            );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event): void {
                $offer = $event->getData();
                if (!$offer instanceof Offer) {
                    return;
                }
                $client = $offer->getClient();
                if (!$client instanceof Client || '' === (string)$client->getSupplierNumber()) {
                    return;
                }
                $event->getForm()
                    ->add(
                        'supplierNumber',
                        TextType::class,
                        [
                            'data' => $client->getSupplierNumber(),
                            'disabled' => true,
                            'label' => 'field.supplier_number',
                            'mapped' => false,
                        ]
                    );
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Offer::class,
            ]
        );
    }
}
