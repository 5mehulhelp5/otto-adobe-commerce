<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task;

class InstructionsProcess extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'instructions/process';

    private \M2E\Otto\Model\Instruction\Processor $instructionProcessor;

    public function __construct(
        \M2E\Otto\Model\Instruction\Processor $instructionProcessor,
        \M2E\Otto\Model\Cron\Manager $cronManager,
        \M2E\Otto\Model\Synchronization\LogService $syncLogger,
        \M2E\Otto\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Otto\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $taskRepo,
            $resource
        );

        $this->instructionProcessor = $instructionProcessor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->instructionProcessor->process();
    }
}
