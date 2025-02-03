<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom\Otto;

class Status extends \M2E\Otto\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'otto_status';

    public function getLabel(): string
    {
        return (string)__('Status');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_STATUS);
    }

    public function getInputType(): string
    {
        return \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel::VALUE_ELEMENT_TYPE_SELECT;
    }

    public function getValueElementType(): string
    {
        return \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel::VALUE_ELEMENT_TYPE_SELECT;
    }

    public function getOptions(): array
    {
        return [
            [
                'value' => \M2E\Otto\Model\Product::STATUS_NOT_LISTED,
                'label' => __('Not Listed'),
            ],
            [
                'value' => \M2E\Otto\Model\Product::STATUS_LISTED,
                'label' => __('Active'),
            ],
            [
                'value' => \M2E\Otto\Model\Product::STATUS_INACTIVE,
                'label' => __('Inactive'),
            ]
        ];
    }
}
