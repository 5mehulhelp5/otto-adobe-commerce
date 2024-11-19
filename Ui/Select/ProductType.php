<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Select;

class ProductType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];

        $typeNames = [
            \M2E\Otto\Helper\Magento\Product::TYPE_SIMPLE => __('Simple Product')
        ];

        foreach ($typeNames as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }
}
