<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PaymentManager.
 */
class PaymentManager implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PaymentManager constructor.
     *
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
            PaymentRepository::class,
            RequestStack::class,
            TranslatorInterface::class,
        ];
    }

    /**
     * @return StreamedResponse|null
     */
    public function download(): ?StreamedResponse
    {
        /** @var PaymentRepository $repository */
        $repository = $this->container->get(PaymentRepository::class);

        $request = $this->container->get(RequestStack::class)->getCurrentRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $translator = $this->container->get(TranslatorInterface::class);

        /** @var Payment[] $payments */
        $payments = $repository->findBy([], ['operationDate' => 'DESC', 'valueDate' => 'DESC']);

        $formatter = new \IntlDateFormatter($request->getLocale(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);

        try {
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('arial')->setSize(10);
            $spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet = $spreadsheet->getActiveSheet();

            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(30);
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->getColumnDimension('I')->setWidth(30);

            $sheet->setCellValue('A1', $translator->trans('field.operation_date'));
            $sheet->setCellValue('B1', $translator->trans('field.value_date'));
            $sheet->setCellValue('C1', $translator->trans('field.amount'));
            $sheet->setCellValue('D1', $translator->trans('field.currency'));
            $sheet->setCellValue('E1', $translator->trans('field.payment_method'));
            $sheet->setCellValue('F1', $translator->trans('field.third_party'));
            $sheet->setCellValue('G1', $translator->trans('field.bank_name'));
            $sheet->setCellValue('H1', $translator->trans('field.locked'));
            $sheet->setCellValue('I1', $translator->trans('field.comment'));
            $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);

            $rownum = 1;

            foreach ($payments as $payment) {
                ++$rownum;
                $sheet->setCellValue('A'.$rownum, $formatter->format($payment->getOperationDate()));
                if ($payment->getValueDate()) {
                    $sheet->setCellValue('B'.$rownum, $formatter->format($payment->getValueDate()));
                }
                $sheet->setCellValue('C'.$rownum, $payment->getAmount());
                $sheet->setCellValue('D'.$rownum, $payment->getCurrency());
                $sheet->setCellValue('E'.$rownum, $payment->getMethod());
                $sheet->setCellValue('F'.$rownum, $payment->getThirdPartyName());
                $sheet->setCellValue('G'.$rownum, $payment->getBankName());
                $sheet->setCellValue('H'.$rownum, $translator->trans($payment->getLocked() ? 'yes' : 'no'));
                $sheet->setCellValue('I'.$rownum, $payment->getComment());
            }

            $sheet
                ->getStyle('A2:B'.$rownum)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet
                ->getStyle('A2:B'.$rownum)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $sheet
                ->getStyle('C2:C'.$rownum)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
            $sheet->getStyle('I2:I'.$rownum)->getAlignment()->setWrapText(true);

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
