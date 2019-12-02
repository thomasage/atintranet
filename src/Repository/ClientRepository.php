<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

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
            if ('city' === $orderby) {
                $builder->addOrderBy('addressPrimary.city', $reverse ? 'DESC' : 'ASC');
            } elseif ('name' === $orderby) {
                $builder->addOrderBy('client.name', $reverse ? 'DESC' : 'ASC');
            } elseif ('postcode' === $orderby) {
                $builder->addOrderBy('addressPrimary.postcode', $reverse ? 'DESC' : 'ASC');
            } elseif ('status' === $orderby) {
                $builder->addOrderBy('client.active', $reverse ? 'DESC' : 'ASC');
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
