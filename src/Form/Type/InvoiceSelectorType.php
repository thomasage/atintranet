<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Form\DataTransformer\InvoiceToNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceSelector
 * @package App\Form\Type
 */
class InvoiceSelectorType extends AbstractType
{
    /**
     * @var InvoiceToNumberTransformer
     */
    private $transformer;

    /**
     * InvoiceSelector constructor.
     * @param InvoiceToNumberTransformer $transformer
     */
    public function __construct(InvoiceToNumberTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['invalid_message' => 'The selected invoice does not exist']);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return TextType::class;
    }
}
