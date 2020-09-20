<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class InternalExtension.
 */
class InternalExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('arraySumColumn', [$this, 'arraySumColumn']),
        ];
    }

    /**
     * @return float|int
     */
    public function arraySumColumn(array $data, string $column)
    {
        return array_sum(array_column($data, $column));
    }
}
