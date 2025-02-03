<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom\Otto;

class OnlineTitle extends \M2E\Otto\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'otto_online_title';

    public function getLabel(): string
    {
        return (string)__('Title');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_TITLE);
    }
}
