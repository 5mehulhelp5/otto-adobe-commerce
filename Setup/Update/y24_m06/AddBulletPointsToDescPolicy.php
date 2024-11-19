<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use Magento\Framework\DB\Ddl\Table;
use M2E\Otto\Model\ResourceModel\Template\Description as DescriptionResource;

class AddBulletPointsToDescPolicy extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_DESCRIPTION);

        $modifier->addColumn(
            DescriptionResource::COLUMN_BULLET_POINTS,
            Table::TYPE_TEXT,
            null,
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_ATTRIBUTE
        );
    }
}
