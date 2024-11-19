<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Async;

use M2E\Otto\Model\Otto\Listing\Product\Action\Async;
use M2E\Otto\Model\Otto\Listing\Product\Action\Type;
use M2E\Otto\Model\Otto\Listing\Product\Action\ActionLoggerTrait;

abstract class AbstractProcessStart
{
    use ActionLoggerTrait;

    private \M2E\Otto\Model\Product\LockManager $lockManager;
    private \M2E\Otto\Model\Product $listingProduct;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator;
    private \M2E\Otto\Model\Processing\Runner $processingRunner;
    private Async\Processing\InitiatorFactory $processingInitiatorFactory;
    private array $params;
    private int $statusChanger;

    public function initialize(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Logger $actionLogger,
        \M2E\Otto\Model\Product\LockManager $lockManager,
        \M2E\Otto\Model\Product $listingProduct,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator,
        \M2E\Otto\Model\Processing\Runner $processingRunner,
        Async\Processing\InitiatorFactory $processingInitiatorFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer $logBuffer,
        array $params,
        int $statusChanger
    ): void {
        $this->actionLogger = $actionLogger;
        $this->lockManager = $lockManager;
        $this->listingProduct = $listingProduct;
        $this->actionConfigurator = $actionConfigurator;
        $this->processingRunner = $processingRunner;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
        $this->logBuffer = $logBuffer;
        $this->params = $params;
        $this->statusChanger = $statusChanger;
    }

    /**
     * @return \M2E\Otto\Helper\Data::STATUS_SUCCESS | \M2E\Otto\Helper\Data::STATUS_ERROR
     */
    public function process(): int
    {
        if ($this->lockManager->isLocked($this->listingProduct)) {
            $this->actionLogger->logListingProductMessage(
                $this->listingProduct,
                \M2E\Otto\Model\Response\Message::createError(
                    'Another Action is being processed. Try again when the Action is completed.',
                ),
            );

            return \M2E\Otto\Helper\Data::STATUS_ERROR;
        }

        $this->lockManager->lock($this->listingProduct, $this->getActionNick());

        if (!$this->validateListingProduct()) {
            $this->flushActionLogs();
            $this->lockManager->unlock($this->listingProduct);

            return \M2E\Otto\Helper\Data::STATUS_ERROR;
        }

        try {
            // order is important
            $command = $this->getCommand();
            $processParams = $this->getProcessingParams($this->getRequestMetadata());
            $initiator = $this->processingInitiatorFactory->create($command, $processParams);

            $this->beforeProcessingRun();

            $this->processingRunner->run($initiator);
        } catch (\Throwable $e) {
            $this->actionLogger->logListingProductMessage(
                $this->listingProduct,
                \M2E\Otto\Model\Response\Message::createError($e->getMessage())
            );
            $this->lockManager->unlock($this->listingProduct);

            return \M2E\Otto\Helper\Data::STATUS_ERROR;
        }

        return \M2E\Otto\Helper\Data::STATUS_SUCCESS;
    }

    private function validateListingProduct(): bool
    {
        $validationResult = $this->getActionValidator()->validate();

        foreach ($this->getActionValidator()->getMessages() as $messageData) {
            $this->addActionLogMessage(
                \M2E\Otto\Model\Response\Message::create(
                    (string)$messageData['text'],
                    $messageData['type']
                ),
            );
        }

        return $validationResult;
    }

    abstract protected function getActionValidator(): Type\AbstractValidator;

    abstract protected function getCommand(): \M2E\Otto\Model\Connector\CommandProcessingInterface;

    private function getProcessingParams(
        array $requestMetadata
    ): \M2E\Otto\Model\Otto\Listing\Product\Action\Async\Processing\Params {
        $actionLogger = $this->getActionLogger();

        return new \M2E\Otto\Model\Otto\Listing\Product\Action\Async\Processing\Params(
            $this->getListingProduct()->getId(),
            $actionLogger->getActionId(),
            $actionLogger->getAction(),
            $actionLogger->getInitiator(),
            $this->getActionNick(),
            $this->getParams(),
            $requestMetadata,
            $this->getActionConfigurator()->getSerializedData(),
            $this->getStatusChanger()
        );
    }

    abstract protected function getActionNick(): string;

    abstract protected function getRequestMetadata(): array;

    protected function getParams(): array
    {
        return $this->params;
    }

    protected function getListingProduct(): \M2E\Otto\Model\Product
    {
        return $this->listingProduct;
    }

    protected function getAccount(): \M2E\Otto\Model\Account
    {
        return $this->listingProduct->getAccount();
    }

    protected function getActionConfigurator(): \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator
    {
        return $this->actionConfigurator;
    }

    public function setStatusChanger(int $statusChanger): void
    {
        $this->statusChanger = $statusChanger;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    // ----------------------------------------

    protected function beforeProcessingRun(): void
    {
        // do something
    }
}
