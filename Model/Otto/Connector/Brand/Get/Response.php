<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Brand\Get;

class Response
{
    /** @var \M2E\Otto\Model\Otto\Connector\Brand\Brand[] */
    private array $brands;

    public function __construct(array $brands)
    {
        $this->brands = $brands;
    }

    /**
     * @return \M2E\Otto\Model\Otto\Connector\Brand\Brand[]
     */
    public function getBrands(): array
    {
        return $this->brands;
    }
}
