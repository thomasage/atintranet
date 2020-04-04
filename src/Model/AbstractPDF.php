<?php

declare(strict_types=1);

namespace App\Model;

use App\Repository\ParamRepository;
use IntlDateFormatter;
use Locale;
use Psr\Container\ContainerInterface;
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

    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false): void
    {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->SetY(99);
    }

    public function Footer(): void
    {
        $this->SetY(-20);
        $this->SetFont(self::FONT_FAMILY, '', 8);
        $this->MultiCell(0, 4, $this->footer, 0, 'C');
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->container->get(TranslatorInterface::class);
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
}
