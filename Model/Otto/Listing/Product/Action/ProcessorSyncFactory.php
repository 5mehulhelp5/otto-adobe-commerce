<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

class ProcessorSyncFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private LoggerFactory $loggerFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer $logBuffer;
    private \M2E\Otto\Model\Product\LockManager $lockManager;

    public function __construct(
        LoggerFactory $loggerFactory,
        \M2E\Otto\Model\Product\LockManager $lockManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer $logBuffer
    ) {
        $this->objectManager = $objectManager;
        $this->loggerFactory = $loggerFactory;
        $this->lockManager = $lockManager;
        $this->logBuffer = $logBuffer;
    }

    public function createDeleteProcessor(
        \M2E\Otto\Model\Product $listingProduct,
        Configurator $configurator,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Delete\Processor {
        $actionLogger = $this->loggerFactory->create(
            $actionLogId,
            \M2E\Otto\Model\Listing\Log::ACTION_REMOVE_PRODUCT,
            $this->getInitiatorByChanger($statusChanger),
        );

        /** @var Type\Delete\Processor */
        return $this->create(
            Type\Delete\Processor::class,
            $listingProduct,
            $configurator,
            $actionLogger,
            $params,
            $statusChanger
        );
    }

    private function create(
        string $processorClass,
        \M2E\Otto\Model\Product $listingProduct,
        Configurator $configurator,
        Logger $actionLogger,
        array $params,
        int $statusChanger
    ): AbstractSyncProcessor {
        /** @var AbstractSyncProcessor $obj */
        $obj = $this->objectManager->create($processorClass);

        $obj->setListingProduct($listingProduct);
        $obj->setActionConfigurator($configurator);
        $obj->setStatusChanger($statusChanger);
        $obj->setActionLogger($actionLogger);
        $obj->setLockManager($this->lockManager);
        $obj->setParams($params);
        $obj->setLogBuffer($this->logBuffer);

        return $obj;
    }

    // ----------------------------------------

    private function getInitiatorByChanger(int $statusChanger): int
    {
        switch ($statusChanger) {
            case \M2E\Otto\Model\Product::STATUS_CHANGER_UNKNOWN:
                return \M2E\Otto\Helper\Data::INITIATOR_UNKNOWN;
            case \M2E\Otto\Model\Product::STATUS_CHANGER_USER:
                return \M2E\Otto\Helper\Data::INITIATOR_USER;
            default:
                return \M2E\Otto\Helper\Data::INITIATOR_EXTENSION;
        }
    }
}
