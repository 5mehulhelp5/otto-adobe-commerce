<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Template\Description as DescriptionResource;
use Magento\Framework\DB\Ddl\Table;

class AddDescriptionTemplateTable extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public const LONG_COLUMN_SIZE = 16777217;

    public function execute(): void
    {
        $descriptionTemplateTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_DESCRIPTION));

        $descriptionTemplateTable
            ->addColumn(
                DescriptionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_TEMPLATE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 4]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                DescriptionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_custom_template',
                DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE
            )
            ->addIndex(
                'title',
                DescriptionResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($descriptionTemplateTable);
    }
}
