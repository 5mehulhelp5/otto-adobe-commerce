<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Async;

use M2E\Otto\Model\Otto\Listing\Product\Action\ActionLoggerTrait;

abstract class AbstractProcessEnd
{
    use ActionLoggerTrait;

    private \M2E\Otto\Model\Product\LockManager $lockManager;
    private \M2E\Otto\Model\Product $listingProduct;
    private array $params;
    private array $requestMetadata;
    private int $statusChanger;

    public function initialize(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Logger $actionLogger,
        \M2E\Otto\Model\Product\LockManager $lockManager,
        \M2E\Otto\Model\Product $listingProduct,
        \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer $logBuffer,
        array $params,
        array $requestMetadata,
        int $statusChanger,
        array $warningMessages
    ): void {
        $this->actionLogger = $actionLogger;
        $this->lockManager = $lockManager;
        $this->listingProduct = $listingProduct;
        $this->logBuffer = $logBuffer;
        $this->params = $params;
        $this->requestMetadata = $requestMetadata;
        $this->statusChanger = $statusChanger;

        foreach ($warningMessages as $warningMessage) {
            $this->getLogBuffer()->addWarning($warningMessage);
        }
    }

    public function process(array $resultData, array $messages): void
    {
        try {
            $this->processComplete($resultData, $messages);
        } finally {
            $this->flushActionLogs();
            $this->lockManager->unlock($this->listingProduct);
        }
    }

    abstract protected function processComplete(array $resultData, array $messages): void;

    protected function getListingProduct(): \M2E\Otto\Model\Product
    {
        return $this->listingProduct;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    protected function getRequestMetadata(): array
    {
        return $this->requestMetadata;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }
}
