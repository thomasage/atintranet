<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OptionPaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class OptionPaymentMethodRepository.
 */
class OptionPaymentMethodRepository extends ServiceEntityRepository
{
    /**
     * OptionPaymentMethodRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OptionPaymentMethod::class);
    }
}
