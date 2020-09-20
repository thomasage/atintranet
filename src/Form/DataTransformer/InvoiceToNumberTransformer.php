<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class InvoiceToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var InvoiceRepository
     */
    private $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function transform($value): string
    {
        if (!$value instanceof Invoice) {
            return '';
        }

        return (string) $value->getNumberComplete();
    }

    public function reverseTransform($value): ?Invoice
    {
        if (!$value) {
            return null;
        }

        $invoice = $this->repository->findByCompleteNumber($value);
        if (!$invoice instanceof Invoice) {
            throw new TransformationFailedException(sprintf('An invoice with number "%s" does not exist.', $value));
        }

        return $invoice;
    }
}
