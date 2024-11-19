<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Listing;

class Other extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_MOVED_TO_LISTING_PRODUCT_ID = 'moved_to_listing_product_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_IS_INCOMPLETE = 'is_incomplete';
    public const COLUMN_SKU = 'sku';
    public const COLUMN_EAN = 'ean';
    public const COLUMN_MOIN = 'moin';
    public const COLUMN_PRODUCT_REFERENCE = 'product_reference';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_CURRENCY = 'currency';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_VAT = 'vat';
    public const COLUMN_QTY = 'qty';
    public const COLUMN_MEDIA = 'media';
    public const COLUMN_CATEGORY = 'category';
    public const COLUMN_BRAND_ID = 'brand_id';
    public const COLUMN_DELIVERY = 'delivery';
    public const COLUMN_SHIPPING_PROFILE_ID = 'shipping_profile_id';
    public const COLUMN_OTTO_PRODUCT_URL = 'otto_product_url';
    public const COLUMN_QTY_ACTUALIZE_DATE = 'qty_actualize_date';
    public const COLUMN_PRICE_ACTUALIZE_DATE = 'price_actualize_date';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_LISTING_OTHER,
            self::COLUMN_ID
        );
    }
}
