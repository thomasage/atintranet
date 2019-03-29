<?php
declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class InvoiceToNumberTransformer
 * @package App\Form\DataTransformer
 */
class InvoiceToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * InvoiceToNumberTransformer constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Invoice|null $value
     * @return string
     */
    public function transform($value): string
    {
        if (!$value instanceof Invoice) {
            return '';
        }

        return (string)$value->getNumber();
    }

    /**
     * @param string $value
     * @return Invoice|null
     */
    public function reverseTransform($value): ?Invoice
    {
        if (!$value) {
            return null;
        }

        $invoice = $this->em->getRepository(Invoice::class)->findOneBy(['number' => $value]);
        if (!$invoice instanceof Invoice) {
            throw new TransformationFailedException(sprintf('An invoice with number "%s" does not exist.', $value));
        }

        return $invoice;
    }
}
