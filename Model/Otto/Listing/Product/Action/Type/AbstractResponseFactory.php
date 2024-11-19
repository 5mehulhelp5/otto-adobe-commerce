<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type;

abstract class AbstractResponseFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer $logBuffer,
        array $params,
        int $statusChanger,
        array $requestMetadata,
        array $responseData
    ): AbstractResponse {
        /** @var AbstractResponse $obj */
        $obj = $this->objectManager->create($this->getResponseClass());
        $obj->setListingProduct($product);
        $obj->setConfigurator($configurator);
        $obj->setLogBuffer($logBuffer);
        $obj->setParams($params);
        $obj->setStatusChanger($statusChanger);
        $obj->setRequestMetaData($requestMetadata);
        $obj->setResponseData($responseData);

        return $obj;
    }

    abstract protected function getResponseClass(): string;
}
