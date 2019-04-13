<?php

declare(strict_types=1);

namespace App\Entity;

interface RateInterface
{
    public function getHourlyRateOnSite(): ?float;

    public function getHourlyRateOffSite(): ?float;
}
