<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\InvoiceDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class InvoiceDetailRepository
 * @package App\Repository
 */
class InvoiceDetailRepository extends ServiceEntityRepository
{
    /**
     * InvoiceDetailRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InvoiceDetail::class);
    }
}
