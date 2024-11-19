<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Brand;

class Value
{
    public string $name;
    public string $id;

    public function __construct(
        string $name,
        string $id
    ) {
        $this->name = $name;
        $this->id = $id;
    }
}
