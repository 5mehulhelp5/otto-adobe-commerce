<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Category;

class Value
{
    public string $title;
    public array $attributes;
    public string $attributesHash;

    public function __construct(
        string $title,
        array $attributes,
        string $attributesHash
    ) {
        $this->title = $title;
        $this->attributes = $attributes;
        $this->attributesHash = $attributesHash;
    }
}
