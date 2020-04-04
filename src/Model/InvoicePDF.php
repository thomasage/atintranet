<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\InvoiceDetail;

final class InvoicePDF extends AbstractPDF
{
    /**
     * @var Invoice
     */
    private $invoice;

    public function Header(): void
    {
        $translator = $this->getTranslator();

        /** @var Address $address */
        $address = $this->invoice->getAddress();

        /** @var Client $client */
        $client = $this->invoice->getClient();

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);

        $this->writeHeaderCompany();

        $this->writeHeaderAddress($address);

        // Number, date, page
        $this->SetFont(self::FONT_FAMILY, '', 11);
        if ('' !== (string)$client->getSupplierNumber()) {
            $this->SetY(54);
            $this->Cell(35, 6, $translator->trans('field.supplier_number'), 0, 0, 'L');
            $this->Cell(0, 6, $client->getSupplierNumber(), 0, 1, 'L');
        } else {
            $this->SetY(60);
        }
        if ('' !== (string)$this->invoice->getOrderNumber()) {
            $this->SetY(60);
            $this->Cell(35, 6, $translator->trans('field.order_number'), 0, 0, 'L');
            $this->Cell(0, 6, $this->invoice->getOrderNumber(), 0, 1, 'L');
        } else {
            $this->SetY(66);
        }
        $this->SetFont(self::FONT_FAMILY, 'B', 11);
        $this->Cell(35, 6, $translator->trans('invoice_number'), 0, 0, 'L');
        $this->Cell(0, 6, $this->invoice->getNumberComplete(), 0, 1, 'L');
        $this->SetFont(self::FONT_FAMILY, '', 11);
        $this->Cell(35, 6, $translator->trans('field.issue_date'), 0, 0, 'L');
        $this->Cell(0, 6, $this->intl->format($this->invoice->getIssueDate()), 0, 1, 'L');
        $this->Cell(35, 6, $translator->trans('field.due_date'), 0, 0, 'L');
        $this->Cell(0, 6, $this->intl->format($this->invoice->getDueDate()), 0, 1, 'L');
        $this->SetY($this->GetY() - 6);
        $this->MultiCell(
            0,
            6,
            sprintf(
                '%s %s / %s',
                $translator->trans('page'),
                $this->getAliasNumPage(),
                $this->getAliasNbPages()
            ),
            0,
            'R'
        );

        $this->writeDetailsHeader();

        // Footer of details
        $this->SetY(225);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(1);
        if ('invoice' === $this->invoice->getType()) {
            $this->Ln(25);
            $this->SetFont(self::FONT_FAMILY, 'U', 10);
            $this->MultiCell(0, 4.5, $translator->trans('bank_coordinates'), 0, 'L');
            $this->SetFont(self::FONT_FAMILY, '', 10);
            $this->MultiCell(0, 4.5, $this->bankAccount, 0, 'L');
        }
    }

    public function build(Invoice $invoice): void
    {
        $translator = $this->getTranslator();

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
        $this->Cell(160, 7, $translator->trans('field.amount_excluding_tax'), 0, 0, 'R');
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
                $translator->trans('field.tax_amount'),
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
        $this->Cell(160, 7, $translator->trans('field.amount_including_tax'), 0, 0, 'R');
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
}
