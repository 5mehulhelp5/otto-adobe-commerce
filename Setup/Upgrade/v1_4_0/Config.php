<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Upgrade\v1_4_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Otto\Setup\Update\y24_m08\RefactorCategoryTable::class,
            \M2E\Otto\Setup\Update\y24_m08\UpdateProductStatus::class,
            \M2E\Otto\Setup\Update\y24_m09\AddColumnsToShippingTemplateTable::class,
        ];
    }
}
