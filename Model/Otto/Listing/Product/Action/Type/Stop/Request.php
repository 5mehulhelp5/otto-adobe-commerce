<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop;

class Request extends \M2E\Otto\Model\Otto\Listing\Product\Action\AbstractRequest
{
    public function getActionData(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array {
        return [
            'sku' => $product->getOttoProductSku(),
            'action_date' => \M2E\Otto\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
        ];
    }

    protected function getActionMetadata(): array
    {
        return [];
    }
}
