<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProjectRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ProjectRateRepository
 * @package App\Repository
 */
class ProjectRateRepository extends ServiceEntityRepository
{
    /**
     * ProjectRateRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectRate::class);
    }
}
