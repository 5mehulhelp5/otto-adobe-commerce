<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Select;

use M2E\Otto\Model\Product;

class ProductStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    public const STATUS_INCOMPLETE = 'Incomplete';

    public function toOptionArray(): array
    {
        $options = [];

        $statuses = [
            Product::STATUS_NOT_LISTED => Product::getStatusTitle(Product::STATUS_NOT_LISTED),
            Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
            Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
            self::STATUS_INCOMPLETE => Product::getIncompleteStatusTitle(),
        ];

        foreach ($statuses as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }
}
