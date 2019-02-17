<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ClientRepository
 * @package App\Repository
 */
class ClientRepository extends ServiceEntityRepository
{
    /**
     * ClientRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @return Paginator
     */
    public function findBySearch(): Paginator
    {
        $builder = $this
            ->createQueryBuilder('client')
            ->innerJoin('client.addressPrimary', 'addressPrimary')
            ->addSelect('addressPrimary')
            ->addOrderBy('client.name', 'ASC');

        return new Paginator($builder->getQuery());
    }
}
