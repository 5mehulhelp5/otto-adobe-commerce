<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m08;

class UpdateProductStatus extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->getConnection()
             ->update(
                 $this->getFullTableName(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT),
                 ['status' => 8],
                 ['status = ?' => 3]
             );
    }
}
