<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\ProjectRate;
use App\Entity\Task;
use App\Repository\TaskRepository;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TrackerManager
 * @package App\Service
 */
class TrackerManager implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * TrackerManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        return [
            RequestStack::class,
            TaskRepository::class,
            TranslatorInterface::class,
        ];
    }

    /**
     * @param \DateTime $month
     * @param Client $client
     * @return Response|null
     */
    public function export(\DateTime $month, Client $client): ?Response
    {
        $intl = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        /** @var TaskRepository $repository */
        $repository = $this->container->get(TaskRepository::class);
        $translator = $this->container->get(TranslatorInterface::class);

        /** @var Task[] $details */
        $details = $repository->detailByClientAndMonth($client, $month);
        $summary = $repository->summaryByClientAndMonth($client, $month);

        $request = $this->container->get(RequestStack::class)->getCurrentRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $formatter = new \IntlDateFormatter($request->getLocale(), \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $formatter->setPattern('MMMM');

        try {

            $spreadsheet = new Spreadsheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('arial')->setSize(10);
            $spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($translator->trans('summary'));

            $sheet->getColumnDimension('A')->setWidth(40);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);

            $sheet->setCellValue('A1', $translator->trans('field.project'));
            $sheet->setCellValue('B1', $translator->trans('field.hours'));
            $sheet->setCellValue('B2', ucfirst($formatter->format($month)));
            $sheet->setCellValue('D2', $month->format('Y'));
            $sheet->setCellValue('F2', $translator->trans('field.from_start'));
            $sheet->mergeCells('A1:A2');
            $sheet->mergeCells('B1:G1');
            $sheet->mergeCells('B2:C2');
            $sheet->mergeCells('D2:E2');
            $sheet->mergeCells('F2:G2');

            $sheet->getStyle('A1:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:G2')->getFont()->setBold(true);

            $rownum = 2;
            foreach ($summary as $d) {

                $rownum++;
                $sheet->setCellValue('A'.$rownum, $d['project_name']);
                $sheet->setCellValue('B'.$rownum, $d['task_duration_month']);
                $sheet->setCellValue('D'.$rownum, $d['task_duration_year']);
                $sheet->setCellValue('F'.$rownum, $d['task_duration_total']);

            }

            $rownum++;
            $lastrow = $rownum;
            $sheet->setCellValue('B'.$rownum, '=SUM(B3:B'.($rownum - 1).')');
            $sheet->setCellValue('D'.$rownum, '=SUM(D3:D'.($rownum - 1).')');
            $sheet->setCellValue('F'.$rownum, '=SUM(F3:F'.($rownum - 1).')');

            $rownum = 2;
            foreach ($summary as $d) {

                $rownum++;
                $sheet->setCellValue('C'.$rownum, sprintf('=B%d/B%d', $rownum, $lastrow));
                $sheet->setCellValue('E'.$rownum, sprintf('=D%d/D%d', $rownum, $lastrow));
                $sheet->setCellValue('G'.$rownum, sprintf('=F%d/F%d', $rownum, $lastrow));

            }

            $sheet
                ->getStyle('B3:B'.$lastrow)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $sheet
                ->getStyle('C3:C'.$lastrow)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet
                ->getStyle('D3:D'.$lastrow)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $sheet
                ->getStyle('E3:E'.$lastrow)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet
                ->getStyle('F3:F'.$lastrow)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $sheet
                ->getStyle('G3:G'.$lastrow)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($translator->trans('field.details'));

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);

            $sheet->setCellValue('A1', $translator->trans('field.date'));
            $sheet->setCellValue('B1', $translator->trans('field.project'));
            $sheet->setCellValue('C1', $translator->trans('field.task'));
            $sheet->setCellValue('D1', $translator->trans('field.duration'));
            $sheet->setCellValue('E1', $translator->trans('field.hourly_rate'));
            $sheet->setCellValue('F1', $translator->trans('field.budget'));

            $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:F1')->getFont()->setBold(true);

            $coordinates = [];

            $rownum = 1;
            foreach ($details as $detail) {

                /** @var \DateTime $date */
                $date = $detail->getStart();
                $date = $date->format('Y-m-d');
                /** @var Project $project */
                $project = $detail->getProject();
                $rate = $project->getRateOfDate($detail->getStart());

                if (isset($coordinates[$date][$project->getId()][$detail->getName()][$detail->getOnSite()])) {

                    /**
                     * @var Cell $cell
                     */
                    $cell = $sheet->getCell(
                        sprintf(
                            'D%d',
                            $coordinates[$date][$project->getId()][$detail->getName()][$detail->getOnSite()]
                        )
                    );
                    $cell->setValue(sprintf('%s+%f', $cell->getValue(), $detail->getDuration() / 3600));
                    continue;

                }

                $rownum++;
                $sheet->setCellValue('A'.$rownum, $intl->format($detail->getStart()));
                $sheet->setCellValue('B'.$rownum, $project);
                $sheet->setCellValue('C'.$rownum, $detail->getName());
                $sheet->setCellValue('D'.$rownum, sprintf('=%f', $detail->getDuration() / 3600));
                if ($rate instanceof ProjectRate) {
                    if ($detail->getOnSite()) {
                        $sheet->setCellValue('E'.$rownum, $rate->getHourlyRateOnSite());
                    } else {
                        $sheet->setCellValue('E'.$rownum, $rate->getHourlyRateOffSite());
                    }
                }
                $sheet->setCellValue('F'.$rownum, sprintf('=D%d*E%d', $rownum, $rownum));

                $coordinates[$date][$project->getId()][$detail->getName()][$detail->getOnSite()] = $rownum;

            }

            $rownum++;
            $sheet->setCellValue('D'.$rownum, '=SUM(D2:D'.($rownum - 1).')');
            $sheet->setCellValue('F'.$rownum, '=SUM(F2:F'.($rownum - 1).')');

            $sheet
                ->getStyle('D2:D'.$rownum)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $sheet
                ->getStyle('E2:F'.$rownum)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $spreadsheet->setActiveSheetIndex(0);

            $response = new StreamedResponse(
                function () use ($spreadsheet) {
                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');
                    flush();
                }
            );
            $response->headers->set(
                'Content-Type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            $response->headers->set(
                'Content-Disposition',
                HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'Document.xlsx')
            );
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;

        } catch (\Exception $e) {

            return null;

        }
    }
}
