<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class InternalExtension
 * @package App\Twig
 */
class InternalExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('arraySumColumn', [$this, 'arraySumColumn']),
        ];
    }

    /**
     * @param array $data
     * @param string $column
     * @return float|int
     */
    public function arraySumColumn(array $data, string $column)
    {
        return array_sum(array_column($data, $column));
    }
}
