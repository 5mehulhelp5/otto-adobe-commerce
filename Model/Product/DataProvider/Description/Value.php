<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Description;

class Value
{
    public string $description;
    public string $hash;

    public function __construct(
        string $description,
        string $hash
    ) {
        $this->description = $description;
        $this->hash = $hash;
    }
}
