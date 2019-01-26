<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class InvoiceRepository
 * @package App\Repository
 */
class InvoiceRepository extends ServiceEntityRepository
{
    /**
     * InvoiceRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $stop
     * @return array
     */
    public function findStatByClient(?\DateTime $start = null, ?\DateTime $stop = null): array
    {
        $series = [];

        $builder = $this
            ->createQueryBuilder('invoice')
            ->innerJoin('invoice.client', 'client')
            ->addGroupBy('client.id')
            ->addOrderBy('invoice.issueDate', 'ASC')
            ->select('client.name client_name')
            ->addSelect('SUM( invoice.amountExcludingTax ) AS amount');

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

    /**
     * @param string $period
     * @return array
     */
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
            ->addSelect('SUM( invoice.amountExcludingTax ) AS amount');

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

    /**
     * @return Invoice[]
     */
    public function findBySearch(): array
    {
        return $this
            ->createQueryBuilder('i')
            ->innerJoin('i.client', 'c')
            ->addSelect('c')
            ->addOrderBy('i.issueDate', 'DESC')
            ->addOrderBy('i.number', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Invoice $invoice
     * @return string|null
     */
    public function findNextNumber(Invoice $invoice): ?string
    {
        try {

            $result = $this
                ->createQueryBuilder('invoice')
                ->select('MAX( invoice.number ) number')
                ->andWhere('invoice.issueDate LIKE :month')
                ->andWhere('invoice.type = :type')
                ->setParameter(':month', $invoice->getIssueDate()->format('Y-m').'-%')
                ->setParameter(':type', $invoice->getType())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $result) {
                return sprintf('%s001', $invoice->getIssueDate()->format('ym'));
            }

            return sprintf(
                '%s%s',
                substr($result, 0, 4),
                str_pad((string)(substr($result, -3) + 1), 3, '0', STR_PAD_LEFT)
            );

        } catch (NonUniqueResultException $e) {

            return null;

        }
    }
}
