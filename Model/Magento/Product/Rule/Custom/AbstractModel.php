<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom;

abstract class AbstractModel
{
    abstract public function getAttributeCode();

    abstract public function getLabel();

    abstract public function getValueByProductInstance(\Magento\Catalog\Model\Product $product);

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [];
    }
}
