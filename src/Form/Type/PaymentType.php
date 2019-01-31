<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\OptionPaymentMethod;
use App\Entity\Payment;
use App\Repository\OptionPaymentMethodRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentType
 * @package App\Form\Type
 */
class PaymentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'operationDate',
                DateType::class,
                [
                    'label' => 'field.operation_date',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'valueDate',
                DateType::class,
                [
                    'label' => 'field.value_date',
                    'required' => false,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'method',
                EntityType::class,
                [
                    'class' => OptionPaymentMethod::class,
                    'label' => 'field.payment_method',
                    'query_builder' => function (OptionPaymentMethodRepository $er): QueryBuilder {
                        return $er->createQueryBuilder('option')->addOrderBy('option.name', 'ASC');
                    },
                ]
            )
            ->add(
                'amount',
                NumberType::class,
                [
                    'label' => 'field.amount',
                    'required' => true,
                ]
            )
            ->add(
                'currency',
                CurrencyType::class,
                [
                    'label' => 'field.currency',
                    'required' => true,
                ]
            )
            ->add(
                'thirdPartyName',
                TextType::class,
                [
                    'label' => 'field.third_party',
                    'required' => true,
                ]
            )
            ->add(
                'bankName',
                TextType::class,
                [
                    'label' => 'field.bank_name',
                    'required' => false,
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
                'paymentInvoices',
                CollectionType::class,
                [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_type' => PaymentInvoiceType::class,
                    'label' => 'field.invoices',
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
                'data_class' => Payment::class,
            ]
        );
    }
}
