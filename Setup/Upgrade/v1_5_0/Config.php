<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Upgrade\v1_5_0;

class Config implements \M2E\Otto\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Otto\Setup\Update\y24_m09\AddOnlineColumnsToProductTable::class,
        ];
    }
}
