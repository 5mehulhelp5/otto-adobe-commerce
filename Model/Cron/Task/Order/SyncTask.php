<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

use M2E\Otto\Model\Cron\Task\Order\Sync;

class SyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/sync';

    private Sync\OrdersProcessorFactory $ordersProcessorFactory;
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        Sync\OrdersProcessorFactory $ordersProcessorFactory
    ) {
        $this->ordersProcessorFactory = $ordersProcessorFactory;
        $this->accountRepository = $accountRepository;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    /**
     * @param \M2E\Otto\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            try {
                $ordersProcessor = $this->ordersProcessorFactory->create($account, $context->getSynchronizationLog());
                $ordersProcessor->process();
            } catch (\Throwable $e) {
                $context->getExceptionHandler()->processTaskException($e);
            }
        }
    }
}
