<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

abstract class AbstractSyncProcessor
{
    use ActionLoggerTrait;

    private \M2E\Otto\Model\Product\LockManager $lockManager;
    private \M2E\Otto\Model\Product $listingProduct;
    private \M2E\Otto\Model\Account $account;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator;
    private array $params = [];
    private int $statusChanger;

    public function process(): int
    {
        $this->init();

        $this->actionLogger->setStatus(\M2E\Core\Helper\Data::STATUS_SUCCESS);

        if ($this->lockManager->isLocked($this->listingProduct)) {
            $this->actionLogger->logListingProductMessage(
                $this->listingProduct,
                \M2E\Core\Model\Response\Message::createError(
                    'Another Action is being processed. Try again when the Action is completed.',
                ),
            );

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }

        $this->lockManager->lock($this->listingProduct, $this->getActionNick());

        try {
            if (!$this->validateListingProduct()) {
                $this->flushActionLogs();
                $this->lockManager->unlock($this->listingProduct);

                return \M2E\Core\Helper\Data::STATUS_ERROR;
            }

            $apiResponse = $this->makeCall();

            foreach ($apiResponse->getMessageCollection()->getMessages() as $message) {
                $this->addActionLogMessage($message);
            }

            if ($apiResponse->isResultError()) {
                $this->processFail($apiResponse);
            } else {
                $successfulMessage = $this->processSuccess($apiResponse);
                if (!empty($successfulMessage)) {
                    $this->addActionLogMessage(
                        \M2E\Core\Model\Response\Message::createSuccess($successfulMessage)
                    );
                }
            }

            $this->processComplete($apiResponse);
        } finally {
            $this->flushActionLogs();
            $this->lockManager->unlock($this->listingProduct);
        }

        return $this->getResultStatus();
    }

    public function getResultStatus(): int
    {
        return $this->actionLogger->getStatus();
    }

    private function validateListingProduct(): bool
    {
        $validationResult = $this->getActionValidator()->validate();

        foreach ($this->getActionValidator()->getMessages() as $messageData) {
            $this->addActionLogMessage(
                \M2E\Core\Model\Response\Message::create(
                    (string)$messageData['text'],
                    $messageData['type']
                ),
            );
        }

        if ($validationResult) {
            return true;
        }

        return false;
    }

    abstract protected function getActionValidator(): Type\AbstractValidator;

    abstract protected function getActionNick(): string;

    abstract protected function makeCall(): \M2E\Core\Model\Connector\Response;

    /**
     * @param \M2E\Core\Model\Connector\Response $response
     *
     * @return string - successful message
     */
    abstract protected function processSuccess(\M2E\Core\Model\Connector\Response $response): string;

    abstract protected function processFail(\M2E\Core\Model\Connector\Response $response): void;

    protected function processComplete(\M2E\Core\Model\Connector\Response $response): void
    {
    }

    // ----------------------------------------

    private function init(): void
    {
        $this->storedActionLogMessages = [];
        if (
            !isset(
                $this->actionLogger,
                $this->lockManager,
                $this->listingProduct,
                $this->account,
                $this->actionConfigurator,
            )
        ) {
            throw new \LogicException('Processor was not initialized.');
        }
    }

    public function setStatusChanger(int $statusChanger): void
    {
        $this->statusChanger = $statusChanger;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    public function setActionLogger(\M2E\Otto\Model\Otto\Listing\Product\Action\Logger $logger): void
    {
        $this->actionLogger = $logger;
    }

    public function setLogBuffer(\M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer $logBuffer): void
    {
        $this->logBuffer = $logBuffer;
    }

    public function setLockManager(\M2E\Otto\Model\Product\LockManager $lockManager): void
    {
        $this->lockManager = $lockManager;
    }

    public function setListingProduct(\M2E\Otto\Model\Product $listingProduct): void
    {
        $this->listingProduct = $listingProduct;
        $this->account = $this->listingProduct->getAccount();
    }

    protected function getListingProduct(): \M2E\Otto\Model\Product
    {
        return $this->listingProduct;
    }

    protected function getAccount(): \M2E\Otto\Model\Account
    {
        return $this->account;
    }

    public function setActionConfigurator(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator
    ): void {
        $this->actionConfigurator = $configurator;
    }

    protected function getActionConfigurator(): \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator
    {
        return $this->actionConfigurator;
    }
}
