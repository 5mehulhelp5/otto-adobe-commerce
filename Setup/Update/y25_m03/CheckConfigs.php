<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y25_m03;

use M2E\Otto\Model\Product\InspectDirectChanges\Config;
use M2E\Otto\Helper\Module\Configuration;

class CheckConfigs extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $ottoConfigModifier = $this->getConfigModifier(\M2E\Otto\Helper\Module::IDENTIFIER);
        $coreConfigModifier = $this->getConfigModifier(\M2E\Core\Helper\Module::IDENTIFIER);

        $servicingInterval = random_int(43200, 86400);

        $installationConfiguration = [
            ['/', 'is_disabled', '0'],
            ['/', 'environment', 'production'],
            ['/server/', 'application_key', '3e026d03fd42c954fc97c1ec81c492dea5cfa197'],
            ['/cron/', 'mode', '1'],
            ['/cron/', 'runner', 'magento'],
            ['/cron/magento/', 'disabled', '0'],
            ['/cron/task/system/servicing/synchronize/', 'interval', $servicingInterval],
            ['/logs/clearing/listings/', 'mode', '1'],
            ['/logs/clearing/listings/', 'days', '30'],
            ['/logs/clearing/synchronizations/', 'mode', '1'],
            ['/logs/clearing/synchronizations/', 'days', '30'],
            ['/logs/clearing/orders/', 'mode', '1'],
            ['/logs/clearing/orders/', 'days', '90'],
            ['/logs/listings/', 'last_action_id', '0'],
            ['/logs/grouped/', 'max_records_count', '100000'],
            ['/support/', 'contact_email', 'support@m2epro.com'],
            [Configuration::CONFIG_GROUP, 'listing_product_inspector_mode', '0'],
            [Configuration::CONFIG_GROUP, 'view_show_block_notices_mode', '1'],
            [Configuration::CONFIG_GROUP, 'view_show_products_thumbnails_mode', '1'],
            [Configuration::CONFIG_GROUP, 'view_products_grid_use_alternative_mysql_select_mode', '0'],
            [Configuration::CONFIG_GROUP, 'other_pay_pal_url', 'paypal.com/cgi-bin/webscr/'],
            [Configuration::CONFIG_GROUP, 'product_index_mode', '1'],
            [Configuration::CONFIG_GROUP, 'product_force_qty_mode', '0'],
            [Configuration::CONFIG_GROUP, 'product_force_qty_value', '10'],
            [Configuration::CONFIG_GROUP, 'qty_percentage_rounding_greater', '0'],
            [Configuration::CONFIG_GROUP, 'magento_attribute_price_type_converting_mode', '0'],
            [Configuration::CONFIG_GROUP, 'create_with_first_product_options_when_variation_unavailable', '1'],
            [Configuration::CONFIG_GROUP, 'secure_image_url_in_item_description_mode', '0'],
            ['/magento/product/simple_type/', 'custom_types', ''],
            ['/magento/product/downloadable_type/', 'custom_types', ''],
            ['/magento/product/configurable_type/', 'custom_types', ''],
            ['/magento/product/bundle_type/', 'custom_types', ''],
            ['/magento/product/grouped_type/', 'custom_types', ''],
            ['/health_status/notification/', 'mode', 1],
            ['/health_status/notification/', 'email', ''],
            ['/health_status/notification/', 'level', 40],
            [Config::GROUP, Config::KEY_MAX_ALLOWED_PRODUCT_COUNT, '2000'],
            ['/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000'],
            ['/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000'],
        ];

        foreach ($installationConfiguration as $item) {
            [$group, $key, $value] = $item;

            // Remove if config in CORE
            $coreConfigEntity = $coreConfigModifier->getEntity($group, $key);
            if ($coreConfigEntity->getValue() !== null) {
                $coreConfigEntity->delete();
            }

            // Create if config not exist
            $tikTokShopConfigEntity = $ottoConfigModifier->getEntity($group, $key);
            if ($tikTokShopConfigEntity->getValue() === null) {
                $tikTokShopConfigEntity->updateValue($value);
            }
        }
    }
}
