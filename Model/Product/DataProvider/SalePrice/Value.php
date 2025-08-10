<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\SalePrice;

class Value
{
    public float $value;
    public ?\DateTime $startDate;
    public ?\DateTime $endDate;

    public function __construct(
        float $value,
        ?\DateTime $startDate,
        ?\DateTime $endDate
    ) {
        $this->value = $value;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getFormattedStartDate(): ?string
    {
        if ($this->startDate === null) {
            return null;
        }

        return $this->startDate->format('Y-m-d H:i:s');
    }

    public function getFormattedEndDate(): ?string
    {
        if ($this->endDate === null) {
            return null;
        }

        return $this->endDate->format('Y-m-d H:i:s');
    }
}
