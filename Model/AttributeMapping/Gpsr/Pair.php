<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping\Gpsr;

class Pair
{
    public ?int $id;
    public string $type;
    public string $channelAttributeTitle;
    public string $channelAttributeCode;
    public ?string $magentoAttributeCode;

    public function __construct(
        ?int $id,
        string $type,
        string $channelAttributeTitle,
        string $channelAttributeCode,
        ?string $magentoAttributeCode
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->channelAttributeTitle = $channelAttributeTitle;
        $this->channelAttributeCode = $channelAttributeCode;
        $this->magentoAttributeCode = $magentoAttributeCode;
    }
}
