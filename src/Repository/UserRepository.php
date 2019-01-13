<?php
declare(strict_types=1);

namespace App\Repository;

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
     * @return Paginator
     */
    public function findBySearch(): Paginator
    {
        $builder = $this
            ->createQueryBuilder('user')
            ->leftJoin('user.client', 'client')
            ->addOrderBy('user.username', 'ASC')
            ->addSelect('client');

        return new Paginator($builder);
    }
}
