<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

trait ActionLoggerTrait
{
    /** @var \M2E\Otto\Model\Response\Message[] */
    private array $storedActionLogMessages = [];
    private Logger $actionLogger;
    private LogBuffer $logBuffer;

    protected function logMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->addActionLogMessage($message);
        }
    }

    protected function addActionLogMessage(\M2E\Otto\Model\Response\Message $message): void
    {
        $this->storedActionLogMessages[] = $message;
    }

    protected function addActionErrorLog(string $message): void
    {
        $this->addActionLogMessage(\M2E\Otto\Model\Response\Message::createError($message));
    }

    protected function addActionWarningLog(string $message): void
    {
        $this->addActionLogMessage(\M2E\Otto\Model\Response\Message::createWarning($message));
    }

    protected function getActionLogger(): Logger
    {
        return $this->actionLogger;
    }

    protected function getLogBuffer(): LogBuffer
    {
        return $this->logBuffer;
    }

    protected function flushActionLogs(): void
    {
        $this->collectActionLogsFromBuffer();

        foreach ($this->storedActionLogMessages as $message) {
            $this->actionLogger->logListingProductMessage(
                $this->listingProduct,
                $message,
            );
        }

        $this->storedActionLogMessages = [];
    }

    private function collectActionLogsFromBuffer(): void
    {
        foreach ($this->logBuffer->getLogs() as $messageData) {
            $this->addActionLogMessage(
                \M2E\Otto\Model\Response\Message::create(
                    $messageData->getMessage(),
                    $messageData->getSeverity()
                ),
            );
        }
    }
}
