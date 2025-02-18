<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Upgrade\v1_4_1;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Otto\Setup\Update\y24_m09\AddIsIncompleteColumns::class,
            \M2E\Otto\Setup\Update\y24_m09\FixTablesStructure::class,
            \M2E\Otto\Setup\Update\y24_m09\RemoveUniqueConstraintFromEanColumn::class,
        ];
    }
}
