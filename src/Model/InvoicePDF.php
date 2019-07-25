<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\InvoiceDetail;
use App\Entity\Param;
use App\Repository\ParamRepository;
use IntlDateFormatter;
use Locale;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Contracts\Translation\TranslatorInterface;
use TCPDF;

class InvoicePDF extends TCPDF
{
    private const FONT_FAMILY = 'helvetica';

    /**
     * @var string
     */
    private $bankAccount;

    /**
     * @var string
     */
    private $companyAddress;

    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $footer;

    /**
     * @var IntlDateFormatter
     */
    private $intl;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ParamRepository $repository, TranslatorInterface $translator, string $currency)
    {
        parent::__construct();
        $this->SetAuthor($this->companyName);

        /** @var Param[] $params */
        $params = $repository->findAll();
        foreach ($params as $param) {
            if ('company_address' === $param->getCode()) {
                $this->companyAddress = $param->getValue();
            } elseif ('company_name' === $param->getCode()) {
                $this->companyName = $param->getValue();
            } elseif ('invoice_bank_account' === $param->getCode()) {
                $this->bankAccount = $param->getValue();
            } elseif ('invoice_footer' === $param->getCode()) {
                $this->footer = $param->getValue();
            }
        }

        $this->currency = Currencies::getSymbol($currency);
        $this->intl = new IntlDateFormatter(
            Locale::getDefault(), IntlDateFormatter::LONG, IntlDateFormatter::NONE
        );
        $this->translator = $translator;
    }

    public function Header(): void
    {
        /** @var Address $address */
        $address = $this->invoice->getAddress();
        /** @var Client $client */
        $client = $this->invoice->getClient();

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);

        // Company
        $this->Rect(10, 10, 60, 25, 'D');
        $this->Rect(10, 10, 60, 25, 'D');
        $this->SetXY(11, 13);
        $this->SetFont(self::FONT_FAMILY, 'B', 11);
        $this->MultiCell(60, 5, $this->companyName, 0, 'C');
        $this->SetXY(11, 19);
        $this->SetFont(self::FONT_FAMILY, '', 10);
        $this->MultiCell(60, 4.5, $this->companyAddress, 0, 'C');

        // Client
        $this->SetFont(self::FONT_FAMILY, 'B', 12);
        $this->SetXY(110, 40);
        $this->MultiCell(0, 6, $address->getName(), 0, 'L');
        $this->SetFont(self::FONT_FAMILY, '', 12);
        $this->SetX(110);
        $this->MultiCell(
            0,
            6,
            sprintf(
                "%s\n%s %s\n%s",
                $address->getAddress(),
                $address->getPostcode(),
                $address->getCity(),
                Countries::getName($address->getCountry())
            ),
            0,
            'L'
        );

        // Number, date, page
        $this->SetFont(self::FONT_FAMILY, '', 11);
        if ('' !== (string)$client->getSupplierNumber()) {
            $this->SetY(54);
            $this->Cell(35, 6, $this->translator->trans('field.supplier_number'), 0, 0, 'L');
            $this->Cell(0, 6, $client->getSupplierNumber(), 0, 1, 'L');
        } else {
            $this->SetY(60);
        }
        if ('' !== (string)$this->invoice->getOrderNumber()) {
            $this->SetY(60);
            $this->Cell(35, 6, $this->translator->trans('field.order_number'), 0, 0, 'L');
            $this->Cell(0, 6, $this->invoice->getOrderNumber(), 0, 1, 'L');
        } else {
            $this->SetY(66);
        }
        $this->SetFont(self::FONT_FAMILY, 'B', 11);
        $this->Cell(35, 6, $this->translator->trans('invoice_number'), 0, 0, 'L');
        $this->Cell(0, 6, $this->invoice->getNumberComplete(), 0, 1, 'L');
        $this->SetFont(self::FONT_FAMILY, '', 11);
        $this->Cell(35, 6, $this->translator->trans('field.issue_date'), 0, 0, 'L');
        $this->Cell(0, 6, $this->intl->format($this->invoice->getIssueDate()), 0, 1, 'L');
        $this->Cell(35, 6, $this->translator->trans('field.due_date'), 0, 0, 'L');
        $this->Cell(0, 6, $this->intl->format($this->invoice->getDueDate()), 0, 1, 'L');
        $this->SetY($this->GetY() - 6);
        $this->MultiCell(
            0,
            6,
            sprintf(
                '%s %s / %s',
                $this->translator->trans('page'),
                $this->getAliasNumPage(),
                $this->getAliasNbPages()
            ),
            0,
            'R'
        );

        // Headers of details
        $this->SetFont(self::FONT_FAMILY, '', 11);
        $this->SetY(90);
        $this->Cell(110, 7, $this->translator->trans('field.designation'), 0, 0, 'L');
        $this->Cell(20, 7, $this->translator->trans('field.quantity'), 0, 0, 'R');
        $this->Cell(30, 7, $this->translator->trans('field.amount_unit'), 0, 0, 'R');
        $this->Cell(30, 7, $this->translator->trans('field.amount_excluding_tax'), 0, 0, 'R');
        $this->Line(10, $this->GetY() + 8, 200, $this->GetY() + 8);

        // Footer of details
        $this->SetY(225);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(1);
        if ('invoice' === $this->invoice->getType()) {
            $this->Ln(25);
            $this->SetFont(self::FONT_FAMILY, 'U', 10);
            $this->MultiCell(0, 4.5, $this->translator->trans('bank_coordinates'), 0, 'L');
            $this->SetFont(self::FONT_FAMILY, '', 10);
            $this->MultiCell(0, 4.5, $this->bankAccount, 0, 'L');
        }
    }

    public function Footer(): void
    {
        $this->SetY(-20);
        $this->SetFont(self::FONT_FAMILY, '', 8);
        $this->MultiCell(0, 4, $this->footer, 0, 'C');
    }

    public function build(Invoice $invoice): void
    {
        $this->invoice = $invoice;

        $this->AddPage();

        $this->SetFont(self::FONT_FAMILY, '', 11);
        foreach ($this->invoice->getDetails() as $detail) {
            if (!$detail instanceof InvoiceDetail) {
                continue;
            }
            $designation = $this->stringToArray($detail->getDesignation(), 90);
            foreach ($designation as $k => $v) {
                if ($this->GetY() > 215) {
                    $this->AddPage();
                }
                $this->Cell(110, 6, $v, 0, 0, 'L');
                if (0 === $k) {
                    $this->Cell(20, 6, $detail->getQuantity(), 0, 0, 'R');
                    $this->Cell(
                        30,
                        6,
                        sprintf(
                            '%s %s',
                            number_format((float)$detail->getAmountUnit(), 2, '.', ' '),
                            $this->currency
                        ),
                        0,
                        0,
                        'R'
                    );
                    $this->Cell(
                        30,
                        6,
                        sprintf(
                            '%s %s',
                            number_format((float)$detail->getAmountTotal(), 2, '.', ' '),
                            $this->currency
                        ),
                        0,
                        0,
                        'R'
                    );
                }
                $this->Ln();
            }
        }

        if ('' !== (string)$this->invoice->getComment()) {
            $this->Ln();
            if ($this->GetY() > 215) {
                $this->AddPage();
            }
            $this->SetFont(self::FONT_FAMILY, 'I', 11);
            $this->MultiCell(0, 5, $this->invoice->getComment(), 0, 'L');
        }

        $this->SetFont(self::FONT_FAMILY, '', 11);
        $this->SetY(226);
        $this->Cell(160, 7, $this->translator->trans('field.amount_excluding_tax'), 0, 0, 'R');
        $this->Cell(
            30,
            7,
            sprintf(
                '%s %s',
                number_format(
                    $this->invoice->getAmountExcludingTax() * ('credit' === $this->invoice->getType() ? -1 : 1),
                    2,
                    '.',
                    ' '
                ),
                $this->currency
            ),
            0,
            1,
            'R'
        );
        $this->Cell(
            160,
            7,
            sprintf(
                '%s %s%%',
                $this->translator->trans('field.tax_amount'),
                number_format($this->invoice->getTaxRate() * 100, 2, '.', ' ')
            ),
            0,
            0,
            'R'
        );
        $this->Cell(
            30,
            7,
            sprintf(
                '%s %s',
                number_format(
                    $this->invoice->getTaxAmount() * ('credit' === $this->invoice->getType() ? -1 : 1),
                    2,
                    '.',
                    ' '
                ),
                $this->currency
            ),
            0,
            1,
            'R'
        );
        $this->SetFont(self::FONT_FAMILY, 'B', 12);
        $this->Cell(160, 7, $this->translator->trans('field.amount_including_tax'), 0, 0, 'R');
        $this->Cell(
            30,
            7,
            sprintf(
                '%s %s',
                number_format(
                    $this->invoice->getAmountIncludingTax() * ('credit' === $this->invoice->getType() ? -1 : 1),
                    2,
                    '.',
                    ' '
                ),
                $this->currency
            ),
            0,
            1,
            'R'
        );
    }

    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false): void
    {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->SetY(99);
    }

    private function stringToArray(string $input, int $maxWidth): array
    {
        $output = [''];
        $index = 0;
        foreach (explode("\n", $input) as $paragraph) {
            foreach (explode("\n", wordwrap($paragraph, 1)) as $w => $word) {
                if (ceil($this->GetStringWidth(trim($output[$index].' '.$word))) > $maxWidth) {
                    $output[++$index] = '';
                }
                $output[$index] = trim($output[$index].' '.$word);
            }
            $output[++$index] = '';
        }
        array_pop($output);

        return $output;
    }
}
