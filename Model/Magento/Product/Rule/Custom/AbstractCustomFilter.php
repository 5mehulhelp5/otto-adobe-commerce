<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom;

abstract class AbstractCustomFilter
{
    abstract public function getLabel(): string;

    abstract public function getValueByProductInstance(\Magento\Catalog\Model\Product $product);

    public function getInputType(): string
    {
        return \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel::INPUT_TYPE_STRING;
    }

    public function getValueElementType(): string
    {
        return \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel::VALUE_ELEMENT_TYPE_TEXT;
    }

    public function getOptions(): array
    {
        return [];
    }
}
