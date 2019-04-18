<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findStatByClient(?\DateTime $start = null, ?\DateTime $stop = null): array
    {
        $series = [];

        $builder = $this
            ->createQueryBuilder('invoice')
            ->innerJoin('invoice.client', 'client')
            ->addGroupBy('client.id')
            ->addOrderBy('invoice.issueDate', 'ASC')
            ->select('client.name client_name')
            ->addSelect('SUM( invoice.amountExcludingTax ) * IF( invoice.type = \'credit\', -1, 1 ) AS amount');

        if ($start instanceof \DateTime) {
            $builder
                ->andWhere('invoice.issueDate >= :start')
                ->setParameter('start', $start);
        }

        if ($stop instanceof \DateTime) {
            $builder
                ->andWhere('invoice.issueDate <= :stop')
                ->setParameter('stop', $stop);
        }

        foreach ($builder->getQuery()->getArrayResult() as $result) {
            $series[0]['data'][] = (object)[
                'name' => $result['client_name'],
                'y' => (float)$result['amount'],
            ];
        }

        return ['series' => $series];
    }

    public function findStatByPeriod(string $period = 'm'): array
    {
        $categories = $series = [];

        try {
            if ('y' === $period) {
                $start = new \DateTime('-9 years');
                $stop = new \DateTime('+1 second');
                $interval = 'P1Y';
                $format = 'Y';
            } else {
                $start = new \DateTime('-11 months');
                $stop = new \DateTime('+1 second');
                $interval = 'P1M';
                $format = 'Y-m';
            }
            $start->setTime(0, 0);
            $stop->setTime(23, 59, 59);

            foreach (new \DatePeriod($start, new \DateInterval($interval), $stop) as $d) {
                if (!$d instanceof \DateTime) {
                    continue;
                }
                $categories[] = $d->format($format);
            }
        } catch (\Exception $e) {
            return [
                'categories' => [],
                'series' => [],
            ];
        }

        $countCategories = count($categories);

        $builder = $this
            ->createQueryBuilder('invoice')
            ->innerJoin('invoice.client', 'client')
            ->addGroupBy('period')
            ->addGroupBy('client.id')
            ->addOrderBy('client.name', 'ASC')
            ->select('client.name client_name')
            ->addSelect('SUM( invoice.amountExcludingTax * IF( invoice.type = \'credit\', -1, 1 ) ) AS amount')
            ->andWhere('invoice.issueDate >= :start')
            ->andWhere('invoice.issueDate <= :stop')
            ->setParameter('start', $start)
            ->setParameter('stop', $stop);

        if ('y' === $period) {
            $builder->addSelect('SUBSTRING( invoice.issueDate, 1, 4 ) AS period');
        } else {
            $builder->addSelect('SUBSTRING( invoice.issueDate, 1, 7 ) AS period');
        }

        foreach ($builder->getQuery()->getArrayResult() as $result) {
            if (!isset($series[$result['client_name']])) {
                $series[$result['client_name']] = [
                    'name' => $result['client_name'],
                    'data' => array_fill(0, $countCategories, 0.0),
                ];
            }

            $c = array_search($result['period'], $categories, true);
            $series[$result['client_name']]['data'][$c] = (float)$result['amount'];
        }

        return [
            'categories' => $categories,
            'series' => array_values($series),
        ];
    }

    public function findBySearch(Search $search): Paginator
    {
        $builder = $this
            ->createQueryBuilder('i')
            ->innerJoin('i.client', 'c')
            ->addSelect('c');

        if (null !== ($client = $search->getFilter('client'))) {
            $builder
                ->andWhere('c.id = :client')
                ->setParameter('client', $client);
        }
        if (null !== ($type = $search->getFilter('type'))) {
            $builder
                ->andWhere('i.type = :type')
                ->setParameter('type', $type);
        }

        foreach ($search->getOrderby() as $orderby => $reverse) {
            if ('amountExcludingTax' === $orderby) {
                $builder->addOrderBy('i.amountExcludingTax', $reverse ? 'DESC' : 'ASC');
            } elseif ('amountIncludingTax' === $orderby) {
                $builder->addOrderBy('i.amountIncludingTax', $reverse ? 'DESC' : 'ASC');
            } elseif ('amountPaid' === $orderby) {
                $builder->addOrderBy('i.amountPaid', $reverse ? 'DESC' : 'ASC');
            } elseif ('client' === $orderby) {
                $builder->addOrderBy('c.name', $reverse ? 'DESC' : 'ASC');
            } elseif ('issueDate' === $orderby) {
                $builder->addOrderBy('i.issueDate', $reverse ? 'DESC' : 'ASC');
            } elseif ('number' === $orderby) {
                $builder->addOrderBy('i.number', $reverse ? 'DESC' : 'ASC');
            } elseif ('type' === $orderby) {
                $builder->addOrderBy('i.type', $reverse ? 'DESC' : 'ASC');
            }
        }
        $builder->addOrderBy('i.id', 'DESC');

        if (null !== $search->getResultsPerPage()) {
            $builder
                ->setFirstResult($search->getPage() * $search->getResultsPerPage())
                ->setMaxResults($search->getResultsPerPage());
        }

        return new Paginator($builder->getQuery());
    }

    public function findNextNumber(Invoice $invoice): ?string
    {
        try {
            $result = $this
                ->createQueryBuilder('invoice')
                ->select('MAX( invoice.number ) number')
                ->andWhere('invoice.issueDate LIKE :year')
                ->andWhere('invoice.type = :type')
                ->setParameter(':year', sprintf('%s-%%', $invoice->getIssueDate()->format('Y')))
                ->setParameter(':type', $invoice->getType())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $result) {
                return '001';
            }

            return str_pad((string)(substr($result, -3) + 1), 3, '0', STR_PAD_LEFT);
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findByCompleteNumber($number, string $type = 'invoice'): ?Invoice
    {
        try {

            return $this->createQueryBuilder('invoice')
                ->andWhere('CONCAT( DATE_FORMAT( invoice.issueDate, \'%y%m\' ), invoice.number ) = :number')
                ->andWhere('invoice.type = :type')
                ->setParameter('number', $number)
                ->setParameter('type', $type)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();

        } catch (NoResultException|NonUniqueResultException $e) {

            return null;

        }
    }
}
