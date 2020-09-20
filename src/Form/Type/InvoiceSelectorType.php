<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\DataTransformer\InvoiceToNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceSelector.
 */
class InvoiceSelectorType extends AbstractType
{
    /**
     * @var InvoiceToNumberTransformer
     */
    private $transformer;

    /**
     * InvoiceSelector constructor.
     */
    public function __construct(InvoiceToNumberTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['invalid_message' => 'The selected invoice does not exist']);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
