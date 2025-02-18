<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Upgrade\v1_0_2;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Otto\Setup\Update\y24_m06\AddMagentoShipmentIdColumnToOrderChange::class,
            \M2E\Otto\Setup\Update\y24_m06\AddProductUrl::class,
        ];
    }
}
