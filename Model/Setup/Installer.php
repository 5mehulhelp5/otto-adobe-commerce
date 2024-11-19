<?php

namespace M2E\Otto\Model\Setup;

use M2E\Otto\Model\ResourceModel\Log\System as LogSystemResource;
use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\Account as AccountResource;
use M2E\Otto\Model\ResourceModel\Instruction as ProductInstructionResource;
use M2E\Otto\Model\ResourceModel\Listing as ListingResource;
use M2E\Otto\Model\ResourceModel\Listing\Log as ListingLogResource;
use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;
use M2E\Otto\Model\ResourceModel\Lock\Item as LockItemResource;
use M2E\Otto\Model\ResourceModel\Lock\Transactional as LockTransactionalResource;
use M2E\Otto\Model\ResourceModel\Order\Change as OrderChangeResource;
use M2E\Otto\Model\ResourceModel\Order\Item as OrderItemResource;
use M2E\Otto\Model\ResourceModel\Order\Note as OrderNoteResource;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;
use M2E\Otto\Model\ResourceModel\Product\Lock as ProductLockResource;
use M2E\Otto\Model\ResourceModel\ScheduledAction as ScheduledActionResource;
use M2E\Otto\Model\ResourceModel\Synchronization\Log as SycnLogResource;
use M2E\Otto\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use M2E\Otto\Model\ResourceModel\Template\SellingFormat as SellingFormatResource;
use M2E\Otto\Model\ResourceModel\Template\Description as DescriptionResource;
use M2E\Otto\Model\ResourceModel\Template\Shipping as ShippingResource;
use M2E\Otto\Model\ResourceModel\Tag as TagResource;
use M2E\Otto\Model\ResourceModel\Tag\ListingProduct\Relation as TagProductRelationResource;
use M2E\Otto\Model\ResourceModel\Listing\Wizard as ListingWizardResource;
use M2E\Otto\Model\ResourceModel\Listing\Wizard\Product as ListingWizardProductResource;
use M2E\Otto\Model\ResourceModel\Listing\Wizard\Step as ListingStepResource;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SetupInterface;
use M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup as CategoryGroupDictionaryResource;
use M2E\Otto\Model\ResourceModel\Dictionary\Category as CategoryDictionaryResource;
use M2E\Otto\Model\ResourceModel\Dictionary\Attribute as AttributeDictionaryResource;
use M2E\Otto\Model\ResourceModel\Category as CategoryResource;
use M2E\Otto\Model\ResourceModel\Category\Attribute as CategoryAttributeResource;
use M2E\Otto\Model\ResourceModel\Brand as BrandResource;

class Installer
{
    public const LONG_COLUMN_SIZE = 16777217;

    private SetupInterface $installer;
    private \Magento\Framework\App\DeploymentConfig $deploymentConfig;
    private \Psr\Log\LoggerInterface $logger;
    private \Magento\Framework\Module\ModuleListInterface $moduleList;
    private \M2E\Otto\Helper\Module\Maintenance $maintenance;
    private TablesHelper $tablesHelper;
    private Database\Modifier\ConfigFactory $modifierConfigFactory;
    /** @var \M2E\Otto\Model\Setup\Repository */
    private Repository $setupRepository;

    public function __construct(
        Repository $setupRepository,
        \M2E\Otto\Model\Setup\Database\Modifier\ConfigFactory $modifierConfigFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \M2E\Otto\Helper\Module\Maintenance $maintenance,
        \M2E\Otto\Helper\Module\Database\Tables $tablesHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \M2E\Otto\Setup\LoggerFactory $loggerFactory
    ) {
        $this->modifierConfigFactory = $modifierConfigFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->maintenance = $maintenance;
        $this->moduleList = $moduleList;
        $this->tablesHelper = $tablesHelper;
        $this->logger = $loggerFactory->create();
        $this->setupRepository = $setupRepository;
    }

    /**
     * Module versions from setup_module magento table uses only by magento for run install or upgrade files.
     * We do not use these versions in setup & upgrade logic (only set correct values to it, using domain_setup table).
     * So version, that presented in $context parameter, is not used.
     *
     * @param SetupInterface $setup
     */
    public function install(SetupInterface $setup): void
    {
        $this->installer = $setup;

        $this->maintenance->enable();
        $this->installer->startSetup();

        try {
            $this->dropTables();

            $this->setupRepository->createTable();
            $setupObject = $this->setupRepository->create(null, $this->getCurrentVersion());

            $this->installSchema();
            $this->installData();
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception, 'source' => 'Install']);

            if (isset($setupObject)) {
                $setupObject->setProfilerData($exception->__toString());

                $this->setupRepository->save($setupObject);
            }

            $this->installer->endSetup();

            return;
        }

        $setupObject->markAsCompleted();
        $this->setupRepository->save($setupObject);

        $this->maintenance->disable();
        $this->installer->endSetup();
    }

    private function dropTables(): void
    {
        $likeCondition = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX)
            . \M2E\Otto\Helper\Module\Database\Tables::PREFIX
            . '%';

        $tables = $this->getConnection()->getTables($likeCondition);

        foreach ($tables as $table) {
            $this->getConnection()->dropTable($table);
        }
    }

    /**
     * @throws \Zend_Db_Exception
     */
    private function installSchema(): void
    {
        #region config
        $moduleConfigTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CONFIG));

        $moduleConfigTable
            ->addColumn(
                'id',
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
                'group',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'key',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('group', 'group')
            ->addIndex('key', 'key')
            ->addIndex('value', 'value')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($moduleConfigTable);
        #endregion

        #region account
        $accountTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ACCOUNT));

        $accountTable
            ->addColumn(
                AccountResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                AccountResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_SERVER_HASH,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_INSTALLATION_ID,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_MODE,
                Table::TYPE_TEXT,
                15,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_MAGENTO_INVOICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => '[]']
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                AccountResource::COLUMN_ORDER_LAST_SYNC,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_INVENTORY_LAST_SYNC,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('title', AccountResource::COLUMN_TITLE)
            ->addIndex('installation_id', AccountResource::COLUMN_INSTALLATION_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($accountTable);
        #endregion

        #region listing
        $listingTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING));

        $listingTable
            ->addColumn(
                ListingResource::COLUMN_ID,
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
                ListingResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_SHIPPING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', ListingResource::COLUMN_ACCOUNT_ID)
            ->addIndex('store_id', ListingResource::COLUMN_STORE_ID)
            ->addIndex('title', ListingResource::COLUMN_TITLE)
            ->addIndex('template_description_id', ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID)
            ->addIndex('template_selling_format_id', ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID)
            ->addIndex('template_synchronization_id', ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID)
            ->addIndex('template_shipping_id', ListingResource::COLUMN_TEMPLATE_SHIPPING_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($listingTable);
        #endregion

        #region listing_log
        $listingLogTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_LOG));

        $listingLogTable
            ->addColumn(
                ListingLogResource::COLUMN_ID,
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
                ListingLogResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_PRODUCT_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ACTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ACTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ListingLogResource::COLUMN_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingLogResource::COLUMN_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ListingLogResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('action', ListingLogResource::COLUMN_ACTION)
            ->addIndex('action_id', ListingLogResource::COLUMN_ACTION_ID)
            ->addIndex('initiator', ListingLogResource::COLUMN_INITIATOR)
            ->addIndex('listing_id', ListingLogResource::COLUMN_LISTING_ID)
            ->addIndex('listing_product_id', ListingLogResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('listing_title', ListingLogResource::COLUMN_LISTING_TITLE)
            ->addIndex('product_id', ListingLogResource::COLUMN_PRODUCT_ID)
            ->addIndex('product_title', ListingLogResource::COLUMN_PRODUCT_TITLE)
            ->addIndex('type', ListingLogResource::COLUMN_TYPE)
            ->addIndex('account_id', ListingLogResource::COLUMN_ACCOUNT_ID)
            ->addIndex('create_date', ListingLogResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($listingLogTable);
        #endregion

        #region product
        $listingProductTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT));

        $listingProductTable
            ->addColumn(
                ListingProductResource::COLUMN_ID,
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
                ListingProductResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_OTTO_PRODUCT_SKU,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_SKU,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_IS_INCOMPLETE,
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_EAN,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductResource::COLUMN_PRODUCT_MOIN,
                Table::TYPE_TEXT,
                50,
                ['nullable' => true]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_PRODUCT_REFERENCE,
                Table::TYPE_TEXT,
                100
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DESCRIPTION,
                Table::TYPE_TEXT,
                40,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_ID,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_NAME,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MPN,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MANUFACTURER,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CURRENCY,
                Table::TYPE_TEXT,
                10
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_VAT,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DELIVERY_DATA,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_SHIPPING_PROFILE_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DELIVERY_TYPE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_OTTO_PRODUCT_URL,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORY,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_IMAGES_DATA,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_SHIPPING_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_SHIPPING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_id', ListingProductResource::COLUMN_LISTING_ID)
            ->addIndex('magento_product_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('product_moin', ListingProductResource::COLUMN_PRODUCT_MOIN)
            ->addIndex('status', ListingProductResource::COLUMN_STATUS)
            ->addIndex('status_changer', ListingProductResource::COLUMN_STATUS_CHANGER)
            ->addIndex('online_category', ListingProductResource::COLUMN_ONLINE_CATEGORY)
            ->addIndex('online_title', ListingProductResource::COLUMN_ONLINE_TITLE)
            ->addIndex('template_category_id', ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID)
            ->addIndex('template_selling_format_mode', ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_MODE)
            ->addIndex('template_selling_format_id', ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID)
            ->addIndex('template_synchronization_mode', ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_MODE)
            ->addIndex('template_synchronization_id', ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID)
            ->addIndex('template_description_mode', ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_MODE)
            ->addIndex('template_description_id', ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_ID)
            ->addIndex('template_shipping_mode', ListingProductResource::COLUMN_TEMPLATE_SHIPPING_MODE)
            ->addIndex('template_shipping_id', ListingProductResource::COLUMN_TEMPLATE_SHIPPING_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($listingProductTable);
        #endregion

        #region listing_product_instruction
        $listingProductInstruction = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_INSTRUCTION));

        $listingProductInstruction
            ->addColumn(
                ProductInstructionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_PRIORITY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_SKIP_UNTIL,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_product_id', ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('type', ProductInstructionResource::COLUMN_TYPE)
            ->addIndex('priority', ProductInstructionResource::COLUMN_PRIORITY)
            ->addIndex('skip_until', ProductInstructionResource::COLUMN_SKIP_UNTIL)
            ->addIndex('create_date', ProductInstructionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($listingProductInstruction);
        #endregion

        #region listing_product_scheduled_action
        $listingProductScheduledAction = $this->getConnection()
                                              ->newTable(
                                                  $this->getFullTableName(
                                                      TablesHelper::TABLE_NAME_PRODUCT_SCHEDULED_ACTION
                                                  )
                                              );
        $listingProductScheduledAction
            ->addColumn(
                ScheduledActionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ACTION_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_IS_FORCE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_TAG,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'listing_product_id',
                [ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex('action_type', ScheduledActionResource::COLUMN_ACTION_TYPE)
            ->addIndex('tag', ScheduledActionResource::COLUMN_TAG)
            ->addIndex('create_date', ScheduledActionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($listingProductScheduledAction);
        #endregion

        #region product_lock
        $productLockTable = $this->getConnection()->newTable(
            $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_LOCK)
        );
        $productLockTable
            ->addColumn(
                ProductLockResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                ProductLockResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ProductLockResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductLockResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('id', ProductLockResource::COLUMN_ID)
            ->addIndex('product_id', ProductLockResource::COLUMN_PRODUCT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($productLockTable);
        #endregion

        #region lock_item
        $lockItemTable = $this->getConnection()->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LOCK_ITEM));
        $lockItemTable
            ->addColumn(
                LockItemResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                LockItemResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                LockItemResource::COLUMN_PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', LockItemResource::COLUMN_NICK)
            ->addIndex('parent_id', LockItemResource::COLUMN_PARENT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($lockItemTable);
        #endregion

        #region lock_transactional
        $lockTransactional = $this->getConnection()->newTable(
            $this->getFullTableName(TablesHelper::TABLE_NAME_LOCK_TRANSACTIONAL)
        );
        $lockTransactional
            ->addColumn(
                LockTransactionalResource::COLUMN_ID,
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
                LockTransactionalResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                LockTransactionalResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', LockTransactionalResource::COLUMN_NICK)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($lockTransactional);
        #endregion

        #region processing
        $processingTable = $this->getConnection()->newTable(
            $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING)
        );
        $processingTable
            ->addColumn(
                'id',
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
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'server_hash',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'stage',
                Table::TYPE_TEXT,
                20,
                ['nullable' => false]
            )
            ->addColumn(
                'handler_nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'params',
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'result_data',
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'result_messages',
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'data_next_part',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true]
            )
            ->addColumn(
                'is_completed',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'expiration_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', 'type')
            ->addIndex('stage', 'stage')
            ->addIndex('is_completed', 'is_completed')
            ->addIndex('expiration_date', 'expiration_date')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($processingTable);
        #endregion

        #region processing_partial_data
        $processingPartialDataTable = $this->getConnection()
                                           ->newTable(
                                               $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_PARTIAL_DATA)
                                           );
        $processingPartialDataTable
            ->addColumn(
                'id',
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
                'processing_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'part_number',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'data',
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addIndex('part_number', 'part_number')
            ->addIndex('processing_id', 'processing_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($processingPartialDataTable);
        #endregion

        #region processing_lock
        $processingLockTable = $this->getConnection()->newTable(
            $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_LOCK)
        );
        $processingLockTable
            ->addColumn(
                'id',
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
                'processing_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'object_nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'object_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'tag',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('processing_id', 'processing_id')
            ->addIndex('object_nick', 'object_nick')
            ->addIndex('object_id', 'object_id')
            ->addIndex('tag', 'tag')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($processingLockTable);
        #endregion

        //#region stop_queue
        $stopQueueTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_STOP_QUEUE));

        $stopQueueTable
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_ID,
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
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_REQUEST_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_processed',
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_IS_PROCESSED,
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($stopQueueTable);
        //#endregion

        #region synchronization_log
        $synchronizationLogTable = $this->getConnection()
                                        ->newTable(
                                            $this->getFullTableName(TablesHelper::TABLE_NAME_SYNCHRONIZATION_LOG)
                                        );
        $synchronizationLogTable
            ->addColumn(
                SycnLogResource::COLUMN_ID,
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
                SycnLogResource::COLUMN_OPERATION_HISTORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SycnLogResource::COLUMN_TASK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SycnLogResource::COLUMN_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SycnLogResource::COLUMN_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                SycnLogResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                SycnLogResource::COLUMN_DETAILED_DESCRIPTION,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                SycnLogResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('initiator', SycnLogResource::COLUMN_INITIATOR)
            ->addIndex('task', SycnLogResource::COLUMN_TASK)
            ->addIndex('operation_history_id', SycnLogResource::COLUMN_OPERATION_HISTORY_ID)
            ->addIndex('type', SycnLogResource::COLUMN_TYPE)
            ->addIndex('create_date', SycnLogResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($synchronizationLogTable);
        #endregion

        #region system_log
        $systemLogTable = $this->getConnection()
                               ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_SYSTEM_LOG));
        $systemLogTable
            ->addColumn(
                LogSystemResource::COLUMN_ID,
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
                LogSystemResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_CLASS,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_DETAILED_DESCRIPTION,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', LogSystemResource::COLUMN_TYPE)
            ->addIndex('class', LogSystemResource::COLUMN_CLASS)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($systemLogTable);
        #endregion

        #region operation_history
        $operationHistoryTable = $this->getConnection()
                                      ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_OPERATION_HISTORY));
        $operationHistoryTable
            ->addColumn(
                'id',
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
                'nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'parent_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                'initiator',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'start_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'end_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'data',
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', 'nick')
            ->addIndex('parent_id', 'parent_id')
            ->addIndex('initiator', 'initiator')
            ->addIndex('start_date', 'start_date')
            ->addIndex('end_date', 'end_date')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($operationHistoryTable);
        #endregion

        #region template_selling_format
        $templateSellingFormatTable = $this
            ->getConnection()
            ->newTable(
                $this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SELLING_FORMAT)
            );
        $templateSellingFormatTable
            ->addColumn(
                SellingFormatResource::COLUMN_ID,
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
                SellingFormatResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_CUSTOM_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_PERCENTAGE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 100]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MODIFICATION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MIN_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MAX_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_MODIFIER,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_custom_template',
                SellingFormatResource::COLUMN_IS_CUSTOM_TEMPLATE
            )
            ->addIndex(
                'title',
                SellingFormatResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($templateSellingFormatTable);
        #endregion

        #region template_synchronization
        $templateSynchronizationTable = $this
            ->getConnection()
            ->newTable(
                $this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SYNCHRONIZATION)
            );

        $templateSynchronizationTable
            ->addColumn(
                SynchronizationResource::COLUMN_ID,
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
                SynchronizationResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_FILTER_USER_LOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_STATUS_DISABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_OUT_OFF_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'title',
                SynchronizationResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($templateSynchronizationTable);
        #endregion

        #region template_description
        $templateDescriptionTable = $this
            ->getConnection()
            ->newTable(
                $this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_DESCRIPTION)
            );
        $templateDescriptionTable
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
                DescriptionResource::COLUMN_BULLET_POINTS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
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
        $this->getConnection()->createTable($templateDescriptionTable);
        #endregion

        #region template_shipping
        $templateShippingTable = $this->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SHIPPING));

        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_ACCOUNT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => null]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_SHIPPING_PROFILE_ID,
            Table::TYPE_TEXT,
            255,
            ['default' => null]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_IS_CUSTOM_TEMPLATE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_MODE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE,
            Table::TYPE_TEXT,
            255,
            ['default' => null]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_TRANSPORT_TIME,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => null],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_ORDER_CUTOFF,
            Table::TYPE_TEXT,
            255,
            ['default' => null]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_WORKING_DAYS,
            Table::TYPE_TEXT,
            255,
            ['default' => '[]']
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_TYPE,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'nullable' => false],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_IS_DELETED,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => 0],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null],
        );
        $templateShippingTable->addIndex('title', ShippingResource::COLUMN_TITLE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($templateShippingTable);
        #endregion

        #region wizard
        $wizardTable = $this->getConnection()
                            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_WIZARD));
        $wizardTable
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                'nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'view',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'step',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addIndex('nick', 'nick')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($wizardTable);
        #endregion

        #region registry
        $registryTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_REGISTRY));
        $registryTable
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                'key',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('key', 'key')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($registryTable);
        #endregion

        # region tag
        $tagTable = $this->getConnection()
                         ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_TAG));
        $tagTable->addColumn(
            TagResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
        );
        $tagTable->addColumn(
            TagResource::COLUMN_ERROR_CODE,
            Table::TYPE_TEXT,
            100,
            ['nullable' => false]
        );
        $tagTable->addColumn(
            TagResource::COLUMN_TEXT,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $tagTable->addColumn(
            'create_date',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false]
        );
        $tagTable->addIndex(
            'error_code',
            TagResource::COLUMN_ERROR_CODE,
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $tagTable->setOption('type', 'INNODB');
        $tagTable->setOption('charset', 'utf8');
        $tagTable->setOption('collate', 'utf8_general_ci');
        $tagTable->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($tagTable);
        #endregion

        # region listing_product_tag_relation
        $listingProductTagRelationTable = $this->getConnection()
                                               ->newTable(
                                                   $this->getFullTableName(
                                                       TablesHelper::TABLE_NAME_PRODUCT_TAG_RELATION
                                                   )
                                               );
        $listingProductTagRelationTable->addColumn(
            TagProductRelationResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $listingProductTagRelationTable->addColumn(
            TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ]
        );
        $listingProductTagRelationTable->addColumn(
            TagProductRelationResource::COLUMN_TAG_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ]
        );
        $listingProductTagRelationTable->addColumn(
            TagProductRelationResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false]
        );
        $listingProductTagRelationTable->addIndex('listing_product_id', TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID);
        $listingProductTagRelationTable->addIndex('tag_id', TagProductRelationResource::COLUMN_TAG_ID);
        $listingProductTagRelationTable->setOption('type', 'INNODB');
        $listingProductTagRelationTable->setOption('charset', 'utf8');
        $listingProductTagRelationTable->setOption('collate', 'utf8_general_ci');
        $listingProductTagRelationTable->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($listingProductTagRelationTable);
        #endregion

        # region order
        $orderTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER))
            ->addColumn(
                'id',
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
                'account_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'magento_order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                ]
            )
            ->addColumn(
                'magento_order_creation_failure',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'magento_order_creation_fails_count',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'magento_order_creation_latest_attempt_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'reservation_state',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'reservation_start_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_TEXT
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Order::COLUMN_OTTO_ORDER_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Order::COLUMN_OTTO_ORDER_NUMBER,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                'order_status',
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                'purchase_create_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'purchase_update_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'paid_amount',
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'currency',
                Table::TYPE_TEXT,
                10,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'tax_details',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'buyer_name',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'buyer_email',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'payment_method_name',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'payment_details',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'shipping_details',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'shipping_date_to',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME
            )
            ->addIndex('otto_order_id', 'otto_order_id')
            ->addIndex('buyer_email', 'buyer_email')
            ->addIndex('buyer_name', 'buyer_name')
            ->addIndex('paid_amount', 'paid_amount')
            ->addIndex('purchase_create_date', 'purchase_create_date')
            ->addIndex('shipping_date_to', 'shipping_date_to')
            ->addIndex('account_id', 'account_id')
            ->addIndex('magento_order_id', 'magento_order_id')
            ->addIndex('magento_order_creation_failure', 'magento_order_creation_failure')
            ->addIndex('magento_order_creation_fails_count', 'magento_order_creation_fails_count')
            ->addIndex(
                'magento_order_creation_latest_attempt_date',
                'magento_order_creation_latest_attempt_date'
            )
            ->addIndex('reservation_state', 'reservation_state')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($orderTable);
        # endregion

        # region order_item
        $orderItemTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_ITEM));

        $orderItemTable
            ->addColumn(
                OrderItemResource::COLUMN_ID,
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
                OrderItemResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_RESERVED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                OrderItemResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_OTTO_ITEM_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderItemResource::COLUMN_OTTO_PRODUCT_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_ARTICLE_NUMBER,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_EAN,
                Table::TYPE_TEXT,
                64,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_PURCHASED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_SALE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_PLATFORM_DISCOUNT,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_TAX_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TRACKING_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('otto_item_id', OrderItemResource::COLUMN_OTTO_ITEM_ID)
            ->addIndex('otto_product_sku', OrderItemResource::COLUMN_OTTO_PRODUCT_SKU)
            ->addIndex('article_number', OrderItemResource::COLUMN_ARTICLE_NUMBER)
            ->addIndex('ean', OrderItemResource::COLUMN_EAN)
            ->addIndex('title', OrderItemResource::COLUMN_TITLE)
            ->addIndex('order_id', OrderItemResource::COLUMN_ORDER_ID)
            ->addIndex('product_id', OrderItemResource::COLUMN_PRODUCT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($orderItemTable);
        # endregion

        # region order_log
        $orderLogTable = $this->getConnection()
                              ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_LOG))
                              ->addColumn(
                                  'id',
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
                                  'account_id',
                                  Table::TYPE_INTEGER,
                                  null,
                                  [
                                      'unsigned' => true,
                                      'nullable' => false,
                                  ]
                              )
                              ->addColumn(
                                  'order_id',
                                  Table::TYPE_INTEGER,
                                  null,
                                  [
                                      'unsigned' => true,
                                      'nullable' => false,
                                  ]
                              )
                              ->addColumn(
                                  'type',
                                  Table::TYPE_SMALLINT,
                                  null,
                                  [
                                      'unsigned' => true,
                                      'nullable' => false,
                                      'default' => 2,
                                  ]
                              )
                              ->addColumn(
                                  'initiator',
                                  Table::TYPE_SMALLINT,
                                  null,
                                  [
                                      'unsigned' => true,
                                      'nullable' => false,
                                      'default' => 2,
                                  ]
                              )
                              ->addColumn(
                                  'description',
                                  Table::TYPE_TEXT
                              )
                              ->addColumn(
                                  'additional_data',
                                  Table::TYPE_VARBINARY
                              )
                              ->addColumn(
                                  'create_date',
                                  Table::TYPE_DATETIME
                              )
                              ->addIndex('account_id', 'account_id')
                              ->addIndex('order_id', 'order_id')
                              ->setOption('type', 'INNODB')
                              ->setOption('charset', 'utf8')
                              ->setOption('collate', 'utf8_general_ci')
                              ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($orderLogTable);
        # endregion

        # region order_note
        $orderNoteTable = $this->getConnection()
                               ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_NOTE))
                               ->addColumn(
                                   OrderNoteResource::COLUMN_ID,
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
                                   OrderNoteResource::COLUMN_ORDER_ID,
                                   Table::TYPE_INTEGER,
                                   null,
                                   [
                                       'unsigned' => true,
                                       'nullable' => false,
                                   ]
                               )
                               ->addColumn(
                                   OrderNoteResource::COLUMN_NOTE,
                                   Table::TYPE_TEXT,
                               )
                               ->addColumn(
                                   OrderNoteResource::COLUMN_UPDATE_DATE,
                                   Table::TYPE_DATETIME
                               )
                               ->addColumn(
                                   OrderNoteResource::COLUMN_CREATE_DATE,
                                   Table::TYPE_DATETIME,
                               )
                               ->addIndex('order_id', OrderNoteResource::COLUMN_ORDER_ID)
                               ->setOption('type', 'INNODB')
                               ->setOption('charset', 'utf8')
                               ->setOption('collate', 'utf8_general_ci')
                               ->setOption('row_format', 'dynamic');
        $this
            ->getConnection()
            ->createTable($orderNoteTable);
        # endregion

        # region order_change
        $orderChangeTable = $this->getConnection()
                                 ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_CHANGE))
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_ID,
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
                                     OrderChangeResource::COLUMN_ORDER_ID,
                                     Table::TYPE_INTEGER,
                                     null,
                                     [
                                         'unsigned' => true,
                                         'nullable' => false,
                                     ]
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_MAGENTO_SHIPMENT_ID,
                                     Table::TYPE_INTEGER,
                                     null,
                                     [
                                         'unsigned' => true,
                                         'nullable' => false,
                                         'default' => '0',
                                     ]
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_ACTION,
                                     Table::TYPE_TEXT,
                                     50,
                                     ['nullable' => false]
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_PARAMS,
                                     Table::TYPE_TEXT
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_CREATOR_TYPE,
                                     Table::TYPE_SMALLINT,
                                     null,
                                     [
                                         'nullable' => false,
                                         'default' => 0,
                                     ]
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
                                     Table::TYPE_SMALLINT,
                                     null,
                                     [
                                         'unsigned' => true,
                                         'nullable' => false,
                                         'default' => 0,
                                     ]
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                                     Table::TYPE_DATETIME,
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_HASH,
                                     Table::TYPE_TEXT,
                                     50
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_UPDATE_DATE,
                                     Table::TYPE_DATETIME
                                 )
                                 ->addColumn(
                                     OrderChangeResource::COLUMN_CREATE_DATE,
                                     Table::TYPE_DATETIME
                                 )
                                 ->addIndex('action', OrderChangeResource::COLUMN_ACTION)
                                 ->addIndex('creator_type', OrderChangeResource::COLUMN_CREATOR_TYPE)
                                 ->addIndex('hash', OrderChangeResource::COLUMN_HASH)
                                 ->addIndex('order_id', OrderChangeResource::COLUMN_ORDER_ID)
                                 ->addIndex('processing_attempt_count', OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT)
                                 ->setOption('type', 'INNODB')
                                 ->setOption('charset', 'utf8')
                                 ->setOption('collate', 'utf8_general_ci')
                                 ->setOption('row_format', 'dynamic');

        $this
            ->getConnection()
            ->createTable($orderChangeTable);
        # endregion

        # region listing_other
        $listingOtherTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_OTHER));

        $listingOtherTable
            ->addColumn(
                ListingOtherResource::COLUMN_ID,
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
                ListingOtherResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_PRODUCT_REFERENCE,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_EAN,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MOIN,
                Table::TYPE_TEXT,
                50,
                ['nullable' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_IS_INCOMPLETE,
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                70,
                ['nullable' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CURRENCY,
                Table::TYPE_TEXT,
                10,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 2],
                ['unsigned' => true, 'nullable' => false, 'default' => '0.00']
            )
            ->addColumn(
                ListingOtherResource::COLUMN_VAT,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MEDIA,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CATEGORY,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_BRAND_ID,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_DELIVERY,
                Table::TYPE_TEXT,
                self::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_SHIPPING_PROFILE_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_OTTO_PRODUCT_URL,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_QTY_ACTUALIZE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_PRICE_ACTUALIZE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('sku', ListingOtherResource::COLUMN_SKU, ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addIndex('ean', ListingOtherResource::COLUMN_EAN)
            ->addIndex('moin', ListingOtherResource::COLUMN_MOIN)
            ->addIndex('account_id', ListingOtherResource::COLUMN_ACCOUNT_ID)
            ->addIndex('product_reference', ListingOtherResource::COLUMN_PRODUCT_REFERENCE)
            ->addIndex('magento_product_id', ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('status', ListingOtherResource::COLUMN_STATUS)
            ->addIndex('title', ListingOtherResource::COLUMN_TITLE)
            ->addIndex('currency', ListingOtherResource::COLUMN_CURRENCY)
            ->addIndex('price', ListingOtherResource::COLUMN_PRICE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($listingOtherTable);
        # endregion

        # region category_group_dictionary
        $categoryGroupDictionaryTable = $this->getConnection()
                                  ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_GROUP_DICTIONARY));
        $categoryGroupDictionaryTable->addColumn(
            CategoryGroupDictionaryResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryGroupDictionaryTable->addColumn(
            CategoryGroupDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $categoryGroupDictionaryTable->addColumn(
            CategoryGroupDictionaryResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $categoryGroupDictionaryTable->addColumn(
            CategoryGroupDictionaryResource::COLUMN_PRODUCT_TITLE_PATTERN,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        )
        ->addIndex('category_group_id', 'category_group_id')
        ->setOption('type', 'INNODB')
        ->setOption('charset', 'utf8')
        ->setOption('collate', 'utf8_general_ci')
        ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryGroupDictionaryTable);
        #endregion

        # region category_dictionary
        $categoryDictionaryTable = $this->getConnection()
                                             ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_DICTIONARY));
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        )
        ->addIndex('category_group_id', 'category_group_id')
        ->setOption('type', 'INNODB')
        ->setOption('charset', 'utf8')
        ->setOption('collate', 'utf8_general_ci')
        ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryDictionaryTable);
        #endregion

        # region category_group_attribute_dictionary
        $categoryGroupAttributeDictionaryTable = $this->getConnection()
                                        ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY));
        $categoryGroupAttributeDictionaryTable->addColumn(
            AttributeDictionaryResource::COLUMN_ID,
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
            AttributeDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_DESCRIPTION,
            Table::TYPE_TEXT,
            null,
            ['default' => null]
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_TYPE,
            Table::TYPE_TEXT,
            30,
            ['nullable' => false]
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_IS_REQUIRED,
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false]
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_IS_MULTIPLE_SELECTED,
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false]
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_ALLOWED_VALUES,
            Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE,
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_EXAMPLE_VALUES,
            Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE,
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_RELEVANCE,
            Table::TYPE_TEXT,
            30,
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_REQUIRED_MEDIA_TYPES,
            Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE,
        )
        ->addColumn(
            AttributeDictionaryResource::COLUMN_UNIT,
            Table::TYPE_TEXT,
            30,
        )
                ->addIndex('category_group_id', 'category_group_id')
                ->setOption('type', 'INNODB')
                ->setOption('charset', 'utf8')
                ->setOption('collate', 'utf8_general_ci')
                ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryGroupAttributeDictionaryTable);
        #endregion

        # region category
        $categoryTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY));

        $categoryTable->addColumn(
            CategoryResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_CATEGORY_GROUP_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_STATE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_USED_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES,
            Table::TYPE_BOOLEAN,
            null,
            ['default' => 0]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_IS_DELETED,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        )
            ->addIndex('category_group_id', 'category_group_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryTable);
        #endregion

        # region category_attributes
        $categoryAttributeTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_ATTRIBUTES));

        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_CATEGORY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_TYPE,
            Table::TYPE_TEXT,
            30
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY_ID,
            Table::TYPE_TEXT,
            50,
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_TITLE,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_DESCRIPTION,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_MODE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_RECOMMENDED,
            Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_CUSTOM_VALUE,
            Table::TYPE_TEXT,
            255,
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_CUSTOM_ATTRIBUTE,
            Table::TYPE_TEXT,
            255,
        );
        $categoryAttributeTable->addIndex(
            'category_id',
            CategoryAttributeResource::COLUMN_CATEGORY_ID,
        )
            ->addIndex('category_group_attribute_dictionary_id', 'category_group_attribute_dictionary_id')
            ->addIndex('category_id', 'category_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryAttributeTable);
        #endregion

        # region brand
        $brandTable = $this->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_BRAND));
        $brandTable->addColumn(
            BrandResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $brandTable->addColumn(
            BrandResource::COLUMN_BRAND_ID,
            Table::TYPE_TEXT,
            100,
            ['unsigned' => true, 'nullable' => false]
        );
        $brandTable->addColumn(
            BrandResource::COLUMN_NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $brandTable->addColumn(
            BrandResource::COLUMN_IS_USABLE,
            Table::TYPE_BOOLEAN,
            255,
            ['nullable' => false,]
        )
            ->addIndex('brand_id', 'brand_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($brandTable);
        #endregion

        #region listing_wizard
        $listingWizardTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD));

        $listingWizardTable
            ->addColumn(
                ListingWizardResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                50,
            )
            ->addColumn(
                ListingWizardResource::COLUMN_CURRENT_STEP_NICK,
                Table::TYPE_TEXT,
                150,
            )
            ->addColumn(
                ListingWizardResource::COLUMN_PRODUCT_COUNT_TOTAL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_PROCESS_START_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_PROCESS_END_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('listing_id', ListingWizardResource::COLUMN_LISTING_ID)
            ->addIndex('is_completed', ListingWizardResource::COLUMN_IS_COMPLETED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($listingWizardTable);
        #endregion

        #region listing_wizard_step
        $stepTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD_STEP));

        $stepTable
            ->addColumn(
                ListingStepResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ],
            )
            ->addColumn(
                ListingStepResource::COLUMN_WIZARD_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingStepResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                150,
            )
            ->addColumn(
                ListingStepResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                \M2E\Otto\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null],
            )
            ->addColumn(
                ListingStepResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingStepResource::COLUMN_IS_SKIPPED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingStepResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingStepResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('wizard_id', ListingStepResource::COLUMN_WIZARD_ID)
            ->addIndex('is_completed', ListingStepResource::COLUMN_IS_COMPLETED)
            ->addIndex('is_skipped', ListingStepResource::COLUMN_IS_SKIPPED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($stepTable);
        #endregion

        #region listing_wizard_product
        $productTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD_PRODUCT));

        $productTable
            ->addColumn(
                ListingWizardProductResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_WIZARD_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addIndex('wizard_id', ListingWizardProductResource::COLUMN_WIZARD_ID)
            ->addIndex('category_id', ListingWizardProductResource::COLUMN_CATEGORY_ID)
            ->addIndex('is_processed', ListingWizardProductResource::COLUMN_IS_PROCESSED)
            ->addIndex(
                'wizard_id_magento_product_id',
                [ListingWizardProductResource::COLUMN_WIZARD_ID, ListingWizardProductResource::COLUMN_MAGENTO_PRODUCT_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE],
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($productTable);
        #endregion
    }

    private function installData(): void
    {
        #region config
        $servicingInterval = random_int(43200, 86400);

        $config = $this->getConfigModifier();

        $config->insert('/', 'is_disabled', '0');
        $config->insert('/', 'environment', 'production');
        $config->insert('/license/', 'key');
        $config->insert('/license/domain/', 'real');
        $config->insert('/license/domain/', 'valid');
        $config->insert('/license/domain/', 'is_valid');
        $config->insert('/license/ip/', 'real');
        $config->insert('/license/ip/', 'valid');
        $config->insert('/license/ip/', 'is_valid');
        $config->insert('/license/info/', 'email');
        $config->insert('/server/', 'application_key', '3e026d03fd42c954fc97c1ec81c492dea5cfa197');
        $config->insert('/server/', 'host', 'https://api.m2epro.com');
        $config->insert('/cron/', 'mode', '1');
        $config->insert('/cron/', 'runner', 'magento');
        $config->insert('/cron/magento/', 'disabled', '0');
        $config->insert('/cron/task/system/servicing/synchronize/', 'interval', $servicingInterval);
        $config->insert('/logs/clearing/listings/', 'mode', '1');
        $config->insert('/logs/clearing/listings/', 'days', '30');
        $config->insert('/logs/clearing/synchronizations/', 'mode', '1');
        $config->insert('/logs/clearing/synchronizations/', 'days', '30');
        $config->insert('/logs/clearing/orders/', 'mode', '1');
        $config->insert('/logs/clearing/orders/', 'days', '90');
        $config->insert('/logs/listings/', 'last_action_id', '0');
        $config->insert('/logs/grouped/', 'max_records_count', '100000');
        $config->insert('/support/', 'contact_email', 'support@m2epro.com');
        $config->insert('/general/configuration/', 'view_show_block_notices_mode', '1');
        $config->insert('/general/configuration/', 'view_show_products_thumbnails_mode', '1');
        $config->insert('/general/configuration/', 'view_products_grid_use_alternative_mysql_select_mode', '0');
        $config->insert('/general/configuration/', 'other_pay_pal_url', 'paypal.com/cgi-bin/webscr/');
        $config->insert('/general/configuration/', 'product_index_mode', '1');
        $config->insert('/general/configuration/', 'product_force_qty_mode', '0');
        $config->insert('/general/configuration/', 'product_force_qty_value', '10');
        $config->insert('/general/configuration/', 'qty_percentage_rounding_greater', '0');
        $config->insert('/general/configuration/', 'magento_attribute_price_type_converting_mode', '0');
        $config->insert(
            '/general/configuration/',
            'create_with_first_product_options_when_variation_unavailable',
            '1'
        );
        $config->insert('/general/configuration/', 'secure_image_url_in_item_description_mode', '0');
        $config->insert('/magento/product/simple_type/', 'custom_types', '');
        $config->insert('/magento/product/downloadable_type/', 'custom_types', '');
        $config->insert('/magento/product/configurable_type/', 'custom_types', '');
        $config->insert('/magento/product/bundle_type/', 'custom_types', '');
        $config->insert('/magento/product/grouped_type/', 'custom_types', '');
        $config->insert('/health_status/notification/', 'mode', 1);
        $config->insert('/health_status/notification/', 'email', '');
        $config->insert('/health_status/notification/', 'level', 40);
        $config->insert('/listing/product/inspector/', 'max_allowed_instructions_count', '2000');
        $config->insert('/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000');
        $config->insert('/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000');
        #endregion

        #region wizard
        $this->getConnection()->insertMultiple(
            $this->getFullTableName(TablesHelper::TABLE_NAME_WIZARD),
            [
                [
                    'nick' => 'installationOtto',
                    'view' => 'otto',
                    'status' => 0,
                    'step' => null,
                    'type' => 1,
                    'priority' => 2,
                ],
            ],
        );
        #endregion

        #region tag
        $tagCreateDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $tagCreateDate = $tagCreateDate->format('Y-m-d H:i:s');

        $this->getConnection()->insertMultiple(
            $this->getFullTableName(TablesHelper::TABLE_NAME_TAG),
            [
                [
                    'error_code' => 'has_error',
                    'text' => 'Has error',
                    'create_date' => $tagCreateDate,
                ],
            ]
        );
        #endregion
    }

    private function getConnection(): AdapterInterface
    {
        return $this->installer->getConnection();
    }

    private function getFullTableName(string $tableName): string
    {
        return $this->tablesHelper->getFullName($tableName);
    }

    protected function getConfigModifier(): \M2E\Otto\Model\Setup\Database\Modifier\Config
    {
        return $this->modifierConfigFactory->create(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_CONFIG,
            $this->installer
        );
    }

    private function getCurrentVersion(): string
    {
        return $this->moduleList->getOne(\M2E\Otto\Helper\Module::IDENTIFIER)['setup_version'];
    }
}
