<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class TaskRepository
 * @package App\Repository
 */
class TaskRepository extends ServiceEntityRepository
{
    /**
     * TaskRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @param User $user
     * @param string $period
     * @return array
     */
    public function findStatByPeriodGroupByProject(User $user, string $period = 'd'): array
    {
        $data = [
            'categories' => [],
            'series' => [],
        ];

        try {

            if ('y' === $period) {
                $start = new \DateTime('-9 years');
                $stop = new \DateTime('+1 second');
                $interval = 'P1Y';
                $format = 'Y';
            } elseif ('m' === $period) {
                $start = new \DateTime('-11 months');
                $stop = new \DateTime('+1 second');
                $interval = 'P1M';
                $format = 'Y-m';
            } elseif ('w' === $period) {
                $start = new \DateTime('-8 weeks');
                $stop = new \DateTime('+1 second');
                $interval = 'P1W';
                $format = 'W';
            } else {
                $start = new \DateTime('-13 days');
                $stop = new \DateTime('+1 second');
                $interval = 'P1D';
                $format = 'd';
            }
            $start->setTime(0, 0, 0);
            $stop->setTime(23, 59, 59);

            foreach (new \DatePeriod($start, new \DateInterval($interval), $stop) as $d) {
                if (!$d instanceof \DateTime) {
                    continue;
                }
                $data['categories'][] = $d->format($format);
            }

        } catch (\Exception $e) {

            return $data;

        }

        $countCategories = count($data['categories']);

        $builder = $this
            ->createQueryBuilder('task')
            ->innerJoin('task.project', 'project')
            ->innerJoin('project.client', 'client')
            ->andWhere('task.start BETWEEN :start AND :stop')
            ->addGroupBy('project.id')
            ->addGroupBy('period')
            ->select('project.name project_name')
            ->addSelect('SUM( UNIX_TIMESTAMP( task.stop ) - UNIX_TIMESTAMP( task.start ) ) task_duration')
            ->addSelect('client.name client_name')
            ->setParameter('start', $start)
            ->setParameter('stop', $stop);

        if ('y' === $period) {
            $builder->addSelect('SUBSTRING( task.start, 1, 4 ) AS period');
        } elseif ('m' === $period) {
            $builder->addSelect('SUBSTRING( task.start, 1, 7 ) AS period');
        } elseif ('w' === $period) {
            $builder->addSelect('DATE_FORMAT( task.start, \'%v\' ) AS period');
        } else {
            $builder->addSelect('DATE_FORMAT( task.start, \'%d\' ) AS period');
        }

        if (($client = $user->getClient()) instanceof Client) {
            $builder
                ->andWhere('project.client = :client')
                ->setParameter(':client', $client);
        }

        foreach ($builder->getQuery()->getArrayResult() as $result) {

            if ($client instanceof Client) {
                $name = $result['project_name'];
            } else {
                $name = sprintf('%s<br/>%s', $result['client_name'], $result['project_name']);
            }

            if (!isset($data['series'][$name])) {
                $data['series'][$name] = [
                    'name' => $name,
                    'data' => array_fill(0, $countCategories, 0.0),
                ];
            }

            $c = array_search($result['period'], $data['categories'], true);
            $data['series'][$name]['data'][$c] = round($result['task_duration'] / 3600, 1);

        }

        return [
            'categories' => $data['categories'],
            'series' => array_values($data['series']),
        ];
    }

    /**
     * @param Client $client
     * @param \DateTimeInterface $month
     * @return array
     */
    public function summaryByClientAndMonth(Client $client, \DateTimeInterface $month): array
    {
        $monthStart = \DateTime::createFromFormat('Y-m-d H:i:s', $month->format('Y-m-01 00:00:00'));
        $monthStop = clone $monthStart;
        $monthStop->modify('+1 month -1 day +23 hours +59 minutes +59 seconds');

        $yearStart = \DateTime::createFromFormat('Y-m-d H:i:s', $monthStop->format('Y-01-01 00:00:00'));
        $yearStop = clone $yearStart;
        $yearStop->modify('+12 months -1 day +23 hours +59 minutes +59 seconds');

        return $this
            ->createQueryBuilder('task')
            ->innerJoin('task.project', 'project')
            ->innerJoin('project.client', 'client')
            ->andWhere('client.id = :client')
            ->select('project.name project_name')
            ->addSelect(
                'SUM( CASE
                     WHEN task.start BETWEEN :month_start AND :month_stop
                     THEN ( UNIX_TIMESTAMP( task.stop ) - UNIX_TIMESTAMP( task.start ) ) / 3600
                 ELSE 0
                 END ) task_duration_month'
            )
            ->addSelect(
                'SUM( CASE
                     WHEN task.start BETWEEN :year_start AND :year_stop
                     THEN ( UNIX_TIMESTAMP( task.stop ) - UNIX_TIMESTAMP( task.start ) ) / 3600
                 ELSE 0
                 END ) task_duration_year'
            )
            ->addSelect(
                'SUM( ( UNIX_TIMESTAMP( task.stop ) - UNIX_TIMESTAMP( task.start ) ) / 3600 ) task_duration_total'
            )
            ->addGroupBy('project.id')
            ->addOrderBy('project.name', 'ASC')
            ->setParameter('client', $client)
            ->setParameter('month_start', $monthStart)
            ->setParameter('month_stop', $monthStop)
            ->setParameter('year_start', $yearStart)
            ->setParameter('year_stop', $yearStop)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param Client $client
     * @param \DateTimeInterface $month
     * @return Task[]
     */
    public function detailByClientAndMonth(Client $client, \DateTimeInterface $month): array
    {
        $monthStart = \DateTime::createFromFormat('Y-m-d H:i:s', $month->format('Y-m-01 00:00:00'));
        $monthStop = clone $monthStart;
        $monthStop->modify('+1 month -1 day +23 hours +59 minutes +59 seconds');

        return $this
            ->createQueryBuilder('task')
            ->innerJoin('task.project', 'project')
            ->leftJoin('project.rates', 'rates')
            ->addSelect('project')
            ->andWhere('project.client = :client')
            ->andWhere('task.start BETWEEN :start AND :stop')
            ->addOrderBy('task.start', 'ASC')
            ->addOrderBy('project.name', 'ASC')
            ->addOrderBy('task.name', 'ASC')
            ->setParameter('client', $client)
            ->setParameter('start', $monthStart)
            ->setParameter('stop', $monthStop)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function findReports(): array
    {
        return $this
            ->createQueryBuilder('task')
            ->innerJoin('task.project', 'project')
            ->innerJoin('project.client', 'client')
            ->select('client.uuid client_uuid')
            ->addSelect('client.name client_name')
            ->addSelect('DATE_FORMAT( task.start, \'%Y-%m\' ) report_date')
            ->addGroupBy('client.id')
            ->addGroupBy('report_date')
            ->addOrderBy('report_date', 'DESC')
            ->addOrderBy('client_name', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }
}
