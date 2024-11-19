<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\ProductReference;

class Result extends \M2E\Otto\Model\Product\DataProvider\AbstractResult
{
    public function getValue(): string
    {
        return $this->value;
    }
}
