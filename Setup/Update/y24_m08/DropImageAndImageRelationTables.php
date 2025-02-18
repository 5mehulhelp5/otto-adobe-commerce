<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m08;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;

class DropImageAndImageRelationTables extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $imageTableName = $this->getFullTableName(TablesHelper::PREFIX . 'image');
        $this->getConnection()->dropTable($imageTableName);

        $imageRelationTableName = $this->getFullTableName(TablesHelper::PREFIX . 'product_image_relation');
        $this->getConnection()->dropTable($imageRelationTableName);
    }
}
