<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Offer;

final class OfferPDF extends AbstractPDF
{
    /**
     * @var Offer
     */
    private $offer;

    public function Header(): void
    {
        $translator = $this->getTranslator();

        /** @var Address $address */
        $address = $this->offer->getAddress();

        /** @var Client $client */
        $client = $this->offer->getClient();

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);

        $this->writeHeaderCompany();

        $this->writeHeaderAddress($address);

        // Number, date, page
        $this->SetFont(self::FONT_FAMILY, '', 11);
        if ('' !== (string) $client->getSupplierNumber()) {
            $this->SetY(54);
            $this->Cell(35, 6, $translator->trans('field.supplier_number'), 0, 0, 'L');
            $this->Cell(0, 6, $client->getSupplierNumber(), 0, 1, 'L');
        } else {
            $this->SetY(60);
        }
        $this->SetY(66);
        $this->SetFont(self::FONT_FAMILY, 'B', 11);
        $this->Cell(35, 6, $translator->trans('offer_number'), 0, 0, 'L');
        $this->Cell(0, 6, $this->offer->getNumberComplete(), 0, 1, 'L');
        $this->SetFont(self::FONT_FAMILY, '', 11);
        $this->Cell(35, 6, $translator->trans('field.issue_date'), 0, 0, 'L');
        $this->Cell(0, 6, $this->intl->format($this->offer->getIssueDate()), 0, 1, 'L');
        $this->Cell(35, 6, $translator->trans('field.validity_date'), 0, 0, 'L');
        $this->Cell(0, 6, $this->intl->format($this->offer->getValidityDate()), 0, 1, 'L');
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
        $this->SetY(245);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(1);
    }

    public function build(Offer $offer): void
    {
        $translator = $this->getTranslator();

        $this->offer = $offer;

        $this->AddPage();

        $this->SetFont(self::FONT_FAMILY, '', 11);
        foreach ($this->offer->getDetails() as $detail) {
            $this->writeDetail($detail);
        }

        if ('' !== (string) $this->offer->getComment()) {
            $this->Ln();
            if ($this->GetY() > 235) {
                $this->AddPage();
            }
            $this->SetFont(self::FONT_FAMILY, 'I', 11);
            $this->MultiCell(0, 5, $this->offer->getComment(), 0, 'L');
        }

        $this->SetFont(self::FONT_FAMILY, '', 11);
        $this->SetY(246);
        $this->Cell(160, 7, $translator->trans('field.amount_excluding_tax'), 0, 0, 'R');
        $this->Cell(
            30,
            7,
            sprintf(
                '%s %s',
                number_format((float) $this->offer->getAmountExcludingTax(), 2, '.', ' '),
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
                number_format($this->offer->getTaxRate() * 100, 2, '.', ' ')
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
                number_format((float) $this->offer->getTaxAmount(), 2, '.', ' '),
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
                number_format((float) $this->offer->getAmountIncludingTax(), 2, '.', ' '),
                $this->currency
            ),
            0,
            1,
            'R'
        );
    }
}
