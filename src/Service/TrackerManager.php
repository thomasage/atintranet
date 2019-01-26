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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @return bool
     */
    public function export(\DateTime $month, Client $client): bool
    {
        $intl = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        $repository = $this->container->get(TaskRepository::class);
        $translator = $this->container->get(TranslatorInterface::class);

        /** @var Task[] $details */
        $details = $repository->detailByClientAndMonth($client, $month);
        $summary = $repository->summaryByClientAndMonth($client, $month);

        $request = $this->container->get(RequestStack::class)->getCurrentRequest();
        if (!$request instanceof Request) {
            return false;
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

            $sheet->setCellValue('A1', $translator->trans('field.project'));
            $sheet->setCellValue('B1', $translator->trans('field.hours'));
            $sheet->setCellValue('B2', ucfirst($formatter->format($month)));
            $sheet->setCellValue('C2', $month->format('Y'));
            $sheet->setCellValue('D2', $translator->trans('field.from_start'));
            $sheet->mergeCells('A1:A2');
            $sheet->mergeCells('B1:D1');

            $sheet->getStyle('A1:D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:D2')->getFont()->setBold(true);

            $rownum = 2;
            foreach ($summary as $d) {

                $rownum++;
                $sheet->setCellValue('A'.$rownum, $d['project_name']);
                $sheet->setCellValue('B'.$rownum, $d['task_duration_month']);
                $sheet->setCellValue('C'.$rownum, $d['task_duration_year']);
                $sheet->setCellValue('D'.$rownum, $d['task_duration_total']);

            }

            $rownum++;
            $sheet->setCellValue('B'.$rownum, '=SUM(B3:B'.($rownum - 1).')');
            $sheet->setCellValue('C'.$rownum, '=SUM(C3:C'.($rownum - 1).')');
            $sheet->setCellValue('D'.$rownum, '=SUM(D3:D'.($rownum - 1).')');

            $sheet
                ->getStyle('B3:D'.$rownum)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

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

                /**
                 * @var Project $project
                 */
                $project = $detail->getProject();
                $rate = $project->getRateOfDate($detail->getStart());

                if (isset($coordinates[$project->getId()][$detail->getName()][$detail->getOnSite()])) {

                    /**
                     * @var Cell $cell
                     */
                    $cell = $sheet->getCell(
                        sprintf(
                            'D%d',
                            $coordinates[$project->getId()][$detail->getName()][$detail->getOnSite()]
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

                $coordinates[$project->getId()][$detail->getName()][$detail->getOnSite()] = $rownum;

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

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Document.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');

            return true;

        } catch (\Exception $e) {

            return false;

        }
    }
}
