<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Brand;

class Brand
{
    private string $name;
    private string $brandId;
    private bool $isUsable;

    public function __construct(
        string $name,
        string $brandId,
        bool $isUsable
    ) {
        $this->name = $name;
        $this->brandId = $brandId;
        $this->isUsable = $isUsable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBrandId(): string
    {
        return $this->brandId;
    }

    public function isUsable(): bool
    {
        return $this->isUsable;
    }
}
