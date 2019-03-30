<?php

declare(strict_types=1);

namespace App\Form\Type\ChoiceType;

use App\Entity\OptionPaymentMethod;
use App\Repository\OptionPaymentMethodRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentMethodChoiceType.
 */
class PaymentMethodChoiceType extends AbstractType
{
    /**
     * @var array
     */
    private $choices;

    /**
     * PaymentMethodChoiceType constructor.
     *
     * @param OptionPaymentMethodRepository $repository
     */
    public function __construct(OptionPaymentMethodRepository $repository)
    {
        /** @var OptionPaymentMethod[] $options */
        $options = $repository->findBy([], ['name' => 'ASC', 'id' => 'ASC']);

        $this->choices = [];
        foreach ($options as $o) {
            $this->choices[(string) $o->getName()] = $o->getId();
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['choices' => $this->choices]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
