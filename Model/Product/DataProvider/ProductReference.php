<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class ProductReference implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'ProductReference';

    public function generate(\M2E\Otto\Model\Product $product): string
    {
        return sha1($product->getMagentoProductId() . $product->getMagentoProduct()->getSku());
    }
}
