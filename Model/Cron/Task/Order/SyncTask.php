<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

use M2E\Otto\Model\Cron\Task\Order\Sync;

class SyncTask extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'order/sync';

    /** @var int in seconds */
    protected int $intervalInSeconds = 300;

    private Sync\OrdersProcessorFactory $ordersProcessorFactory;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        \M2E\Otto\Model\Cron\Manager $cronManager,
        Sync\OrdersProcessorFactory $ordersProcessorFactory,
        \M2E\Otto\Model\Synchronization\LogService $syncLogger,
        \M2E\Otto\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $helperFactory,
            $taskRepo,
            $resource,
        );
        $this->ordersProcessorFactory = $ordersProcessorFactory;
        $this->accountRepository = $accountRepository;
        $this->exceptionHelper = $exceptionHelper;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $synchronizationLog = $this->getSynchronizationLog();
        $synchronizationLog->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            try {
                $ordersProcessor = $this->ordersProcessorFactory->create($account, $synchronizationLog);
                $ordersProcessor->process();
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);
                $synchronizationLog->addFromException($e);
            }
        }
    }
}
