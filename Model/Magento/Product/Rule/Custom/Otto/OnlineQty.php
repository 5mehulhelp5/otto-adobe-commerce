<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom\Otto;

class OnlineQty extends \M2E\Otto\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'otto_online_qty';

    public function getLabel(): string
    {
        return (string)__('Available QTY');
    }

    public function getInputType(): string
    {
        return \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel::INPUT_TYPE_NUMERIC;
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_QTY);
    }
}
