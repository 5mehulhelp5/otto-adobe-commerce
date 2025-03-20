<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class DescriptionProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Description';

    public function getDescription(\M2E\Otto\Model\Product $product): Description\Value
    {
        $data = $product->getRenderedDescription();

        return new Description\Value($data, \M2E\Core\Helper\Data::md5String($data));
    }
}
