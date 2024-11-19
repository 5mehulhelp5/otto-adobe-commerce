<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category\Attribute;

class ProductAttribute extends \M2E\Otto\Model\Category\AbstractAttribute
{
    public function getAttributeType(): string
    {
        return \M2E\Otto\Model\Category\Attribute::ATTRIBUTE_TYPE_PRODUCT;
    }
}
