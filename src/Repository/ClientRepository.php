<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Search;
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
     * @param Search $search
     * @return Paginator
     */
    public function findBySearch(Search $search): Paginator
    {
        $builder = $this
            ->createQueryBuilder('client')
            ->innerJoin('client.addressPrimary', 'addressPrimary')
            ->addSelect('addressPrimary');

        if (null !== ($city = $search->getFilter('city'))) {
            $builder
                ->andWhere('addressPrimary.city LIKE :city')
                ->setParameter('city', '%'.$city.'%');
        }
        if (null !== ($name = $search->getFilter('name'))) {
            $builder
                ->andWhere('client.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        foreach ($search->getOrderby() as $orderby => $reverse) {
            if ('name' === $orderby) {
                $builder->addOrderBy('client.name', $reverse ? 'DESC' : 'ASC');
            }
        }
        $builder->addOrderBy('client.id', 'ASC');

        if (null !== $search->getResultsPerPage()) {
            $builder
                ->setFirstResult($search->getPage() * $search->getResultsPerPage())
                ->setMaxResults($search->getResultsPerPage());
        }

        return new Paginator($builder->getQuery());
    }
}
