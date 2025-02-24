<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

class LogBuffer
{
    /** @var \M2E\Otto\Model\Otto\Listing\Product\Action\LogRecord[] */
    private array $logs = [];

    public function addSuccess($message): void
    {
        $this->logs[] = new LogRecord($message, \M2E\Core\Model\Response\Message::TYPE_SUCCESS);
    }

    public function addWarning($message): void
    {
        $this->logs[] = new LogRecord($message, \M2E\Core\Model\Response\Message::TYPE_WARNING);
    }

    public function addFail($message): void
    {
        $this->logs[] = new LogRecord($message, \M2E\Core\Model\Response\Message::TYPE_ERROR);
    }

    /**
     * @return \M2E\Otto\Model\Otto\Listing\Product\Action\LogRecord[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getWarningMessages(): array
    {
        return array_map(
            static fn(LogRecord $log) => $log->getMessage(),
            array_filter(
                $this->logs,
                static fn(LogRecord $log) => $log->getSeverity() === \M2E\Core\Model\Response\Message::TYPE_WARNING
            )
        );
    }
}
