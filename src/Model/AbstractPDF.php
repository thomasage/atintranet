<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Address;
use App\Entity\RecordDetailInterface;
use App\Repository\ParamRepository;
use IntlDateFormatter;
use Locale;
use Psr\Container\ContainerInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TCPDF;

abstract class AbstractPDF extends TCPDF implements ServiceSubscriberInterface
{
    protected const FONT_FAMILY = 'helvetica';

    /**
     * @var string
     */
    protected $bankAccount;

    /**
     * @var string
     */
    protected $companyAddress;

    /**
     * @var string
     */
    protected $companyName;

    protected $container;

    protected $currency;

    /**
     * @var string
     */
    protected $footer;

    protected $intl;

    public function __construct(ContainerInterface $container, string $currency)
    {
        parent::__construct();

        $this->container = $container;

        $params = $this->getParamRepository()->findAll();
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
        $this->intl = new IntlDateFormatter(Locale::getDefault(), IntlDateFormatter::LONG, IntlDateFormatter::NONE);

        $this->SetAuthor($this->companyName);
    }

    protected function getParamRepository(): ParamRepository
    {
        return $this->container->get(ParamRepository::class);
    }

    public static function getSubscribedServices(): array
    {
        return [
            ParamRepository::class,
            TranslatorInterface::class,
        ];
    }

    public function Footer(): void
    {
        $this->SetY(-20);
        $this->SetFont(self::FONT_FAMILY, '', 8);
        $this->MultiCell(0, 4, $this->footer, 0, 'C');
    }

    protected function writeHeaderCompany(): void
    {
        $this->Rect(10, 10, 60, 25, 'D');
        $this->Rect(10, 10, 60, 25, 'D');
        $this->SetXY(11, 13);
        $this->SetFont(self::FONT_FAMILY, 'B', 11);
        $this->MultiCell(60, 5, $this->companyName, 0, 'C');
        $this->SetXY(11, 19);
        $this->SetFont(self::FONT_FAMILY, '', 10);
        $this->MultiCell(60, 4.5, $this->companyAddress, 0, 'C');
    }

    protected function writeHeaderAddress(Address $address): void
    {
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
    }

    protected function writeDetailsHeader(): void
    {
        $translator = $this->getTranslator();

        $this->SetFont(self::FONT_FAMILY, '', 11);
        $this->SetY(90);
        $this->Cell(110, 7, $translator->trans('field.designation'), 0, 0, 'L');
        $this->Cell(20, 7, $translator->trans('field.quantity'), 0, 0, 'R');
        $this->Cell(30, 7, $translator->trans('field.amount_unit'), 0, 0, 'R');
        $this->Cell(30, 7, $translator->trans('field.amount_excluding_tax'), 0, 0, 'R');
        $this->Line(10, $this->GetY() + 8, 200, $this->GetY() + 8);
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->container->get(TranslatorInterface::class);
    }

    protected function writeDetail(RecordDetailInterface $detail): void
    {
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
                        number_format((float) $detail->getAmountUnit(), 2, '.', ' '),
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
                        number_format((float) $detail->getAmountTotal(), 2, '.', ' '),
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

    protected function stringToArray(string $input, int $maxWidth): array
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

    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false): void
    {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->SetY(99);
    }
}
