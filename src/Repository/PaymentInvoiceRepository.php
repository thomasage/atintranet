<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\PaymentInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class PaymentInvoiceRepository
 * @package App\Repository
 */
class PaymentInvoiceRepository extends ServiceEntityRepository
{
    /**
     * PaymentInvoiceRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaymentInvoice::class);
    }
}
