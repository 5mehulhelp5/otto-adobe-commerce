<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Details;

class Value
{
    public array $bulletPoints;
    public ?string $manufacturer;
    public ?string $mpn;

    public function __construct(
        array $bulletPoints,
        ?string $manufacturer,
        ?string $mpn
    ) {
        $this->bulletPoints = $bulletPoints;
        $this->manufacturer = $manufacturer;
        $this->mpn = $mpn;
    }
}
