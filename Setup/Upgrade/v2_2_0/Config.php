<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Upgrade\v2_2_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Otto\Setup\Update\y25_m02\RemoveOldCronValues::class
        ];
    }
}
