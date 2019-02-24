<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Search;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param Search $search
     * @return Paginator
     */
    public function findBySearch(Search $search): Paginator
    {
        $builder = $this
            ->createQueryBuilder('user')
            ->leftJoin('user.client', 'client')
            ->addSelect('client');

        if (null !== ($username = $search->getFilter('username'))) {
            $builder
                ->andWhere('user.username LIKE :username')
                ->setParameter('username', '%'.$username.'%');
        }

        foreach ($search->getOrderby() as $orderby => $reverse) {
            if ('client' === $orderby) {
                $builder->addOrderBy('client.name', $reverse ? 'DESC' : 'ASC');
            } elseif ('enabled' === $orderby) {
                $builder->addOrderBy('user.enabled', $reverse ? 'DESC' : 'ASC');
            } elseif ('roles' === $orderby) {
                $builder->addOrderBy('user.roles', $reverse ? 'DESC' : 'ASC');
            } elseif ('username' === $orderby) {
                $builder->addOrderBy('user.username', $reverse ? 'DESC' : 'ASC');
            }
        }
        $builder->addOrderBy('user.username', 'ASC');

        if (null !== $search->getResultsPerPage()) {
            $builder
                ->setFirstResult($search->getPage() * $search->getResultsPerPage())
                ->setMaxResults($search->getResultsPerPage());
        }

        return new Paginator($builder);
    }
}
