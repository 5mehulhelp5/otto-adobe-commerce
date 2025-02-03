<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom\Otto;

class OnlineSku extends \M2E\Otto\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'otto_online_sku';

    public function getLabel(): string
    {
        return (string)__('SKU');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SKU);
    }
}
