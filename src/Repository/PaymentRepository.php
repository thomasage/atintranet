<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Payment;
use App\Entity\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findBySearch(Search $search): Paginator
    {
        $builder = $this
            ->createQueryBuilder('payment')
            ->innerJoin('payment.method', 'paymentMethod')
            ->addSelect('paymentMethod');

        if (null !== ($paymentMethod = $search->getFilter('payment_method'))) {
            $builder
                ->andWhere('payment.method = :paymentMethod')
                ->setParameter('paymentMethod', $paymentMethod);
        }
        if (null !== ($thirdParty = $search->getFilter('third_party'))) {
            $builder
                ->andWhere('payment.thirdPartyName LIKE :thirdParty')
                ->setParameter('thirdParty', '%'.$thirdParty.'%');
        }

        foreach ($search->getOrderby() as $orderby => $reverse) {
            if ('amount' === $orderby) {
                $builder->addOrderBy('payment.amount', $reverse ? 'DESC' : 'ASC');
            } elseif ('operation_date' === $orderby) {
                $builder->addOrderBy('payment.operationDate', $reverse ? 'DESC' : 'ASC');
            } elseif ('payment_method' === $orderby) {
                $builder->addOrderBy('paymentMethod.name', $reverse ? 'DESC' : 'ASC');
            } elseif ('third_party' === $orderby) {
                $builder->addOrderBy('payment.thirdPartyName', $reverse ? 'DESC' : 'ASC');
            } elseif ('value_date' === $orderby) {
                $builder->addOrderBy('payment.valueDate', $reverse ? 'DESC' : 'ASC');
            }
        }
        $builder->addOrderBy('payment.id', 'DESC');

        if (null !== $search->getResultsPerPage()) {
            $builder
                ->setFirstResult($search->getPage() * $search->getResultsPerPage())
                ->setMaxResults($search->getResultsPerPage());
        }

        return new Paginator($builder->getQuery());
    }

    public function findAutocompleteThirdPartyName(string $term): array
    {
        return $this->createQueryBuilder('payment')
            ->andWhere('payment.thirdPartyName LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->select('DISTINCT payment.thirdPartyName')
            ->getQuery()
            ->getScalarResult();
    }
}
