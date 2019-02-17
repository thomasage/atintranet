<?php
declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\Intl\Intl;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class LocaleExtension
 * @package App\Twig
 */
class LocaleExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('localizedcountry', [$this, 'localizedCountry']),
            new TwigFilter('localizedduration', [$this, 'localizedDuration']),
        ];
    }

    /**
     * @param int|null $value
     * @return string|null
     */
    public function localizedDuration(?int $value): ?string
    {
        if (null === $value) {
            return null;
        }

        try {
            $from = new \DateTime('@0');
            $to = new \DateTime('@'.$value);
        } catch (\Exception $e) {
            return null;
        }

        $diff = $from->diff($to);

        $hours = $diff->h + $diff->d * 24;
        $minutes = $diff->i;
        $seconds = $diff->s;

        return sprintf(
            '%s:%s:%s',
            str_pad((string)$hours, 2, '0', STR_PAD_LEFT),
            str_pad((string)$minutes, 2, '0', STR_PAD_LEFT),
            str_pad((string)$seconds, 2, '0', STR_PAD_LEFT)
        );
    }

    /**
     * @param null|string $country
     * @return null|string
     */
    public function localizedCountry(?string $country): ?string
    {
        if (null === $country) {
            return null;
        }

        return Intl::getRegionBundle()->getCountryName($country);
    }
}
