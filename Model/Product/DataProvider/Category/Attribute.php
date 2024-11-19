<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Category;

class Attribute
{
    public string $name;
    public array $values;

    public function __construct(
        string $name,
        array $values
    ) {
        $this->name = $name;
        $this->values = $values;
    }
}
