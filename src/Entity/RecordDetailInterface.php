<?php

namespace App\Entity;

interface RecordDetailInterface
{
    public function getAmountTotal(): string;

    public function getAmountUnit(): string;

    public function getDesignation(): ?string;

    public function getQuantity(): float;
}
