<?php

namespace M2E\Otto\Model\Otto\Connector\Attribute\Get;

class Response
{
    /** @var \M2E\Otto\Model\Otto\Connector\Attribute\Attribute[] */
    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return \M2E\Otto\Model\Otto\Connector\Attribute\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
