<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Offer;
use App\Entity\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    public function findBySearch(Search $search): Paginator
    {
        $builder = $this
            ->createQueryBuilder('o')
            ->innerJoin('o.client', 'c')
            ->addSelect('c');

        if (null !== ($client = $search->getFilter('client'))) {
            $builder
                ->andWhere('c.id = :client')
                ->setParameter('client', $client);
        }

        foreach ($search->getOrderby() as $orderby => $reverse) {
            if ('amountExcludingTax' === $orderby) {
                $builder->addOrderBy('o.amountExcludingTax', $reverse ? 'DESC' : 'ASC');
            } elseif ('amountIncludingTax' === $orderby) {
                $builder->addOrderBy('o.amountIncludingTax', $reverse ? 'DESC' : 'ASC');
            } elseif ('client' === $orderby) {
                $builder->addOrderBy('c.name', $reverse ? 'DESC' : 'ASC');
            } elseif ('issueDate' === $orderby) {
                $builder->addOrderBy('o.issueDate', $reverse ? 'DESC' : 'ASC');
            } elseif ('number' === $orderby) {
                $builder->addOrderBy('o.number', $reverse ? 'DESC' : 'ASC');
            }
        }
        $builder
            ->addOrderBy('o.year', 'DESC')
            ->addOrderBy('o.number', 'DESC')
            ->addOrderBy('o.id', 'DESC');

        if (null !== $search->getResultsPerPage()) {
            $builder
                ->setFirstResult($search->getPage() * $search->getResultsPerPage())
                ->setMaxResults($search->getResultsPerPage());
        }

        return new Paginator($builder->getQuery());
    }

    public function findNextNumber(Offer $offer): ?string
    {
        try {
            $result = $this
                ->createQueryBuilder('offer')
                ->select('MAX( offer.number ) number')
                ->andWhere('offer.issueDate LIKE :year')
                ->setParameter(':year', sprintf('%s-%%', $offer->getIssueDate()->format('Y')))
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $result) {
                return '001';
            }

            return str_pad((string) (substr($result, -3) + 1), 3, '0', STR_PAD_LEFT);
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
