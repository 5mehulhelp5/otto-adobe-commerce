<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order\Sync;

use M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByUpdateDate\Processor as ItemsByUpdateDateProcessor;

class OrdersProcessor
{
    private \M2E\Otto\Model\Synchronization\LogService $synchronizationLog;
    private \M2E\Otto\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory;
    private ItemsByUpdateDateProcessor $receiveOrdersProcessor;
    private \M2E\Otto\Model\Account $account;
    private \M2E\Otto\Model\Account\Repository $repository;

    public function __construct(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Synchronization\LogService $logService,
        \M2E\Otto\Model\Account\Repository $repository,
        ItemsByUpdateDateProcessor $receiveOrdersProcessor,
        \M2E\Otto\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory
    ) {
        $this->orderCreatorFactory = $orderCreatorFactory;
        $this->receiveOrdersProcessor = $receiveOrdersProcessor;
        $this->synchronizationLog = $logService;
        $this->account = $account;
        $this->repository = $repository;
    }

    public function process(): void
    {
        $toTime = \M2E\Otto\Helper\Date::createCurrentGmt();
        $fromTime = $this->prepareFromTime($toTime);

        $response = $this->receiveOrdersProcessor->process(
            $this->account,
            $fromTime,
            $toTime
        );

        $this->updateLastOrderSynchronizationDate($response->getToDate());

        $this->processResponseMessages($response->getMessageCollection());

        if (empty($response->getOrders())) {
            return;
        }

        $ordersCreator = $this->orderCreatorFactory->create($this->synchronizationLog);

        $processedOttoOrders = $ordersCreator->processOttoOrders($this->account, $response->getOrders());
        $ordersCreator->processMagentoOrders($processedOttoOrders);
    }

    private function processResponseMessages(
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ): void {
        foreach ($messageCollection->getMessages() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError()
                ? \M2E\Otto\Model\Log\AbstractModel::TYPE_ERROR
                : \M2E\Otto\Model\Log\AbstractModel::TYPE_WARNING;

            $this->synchronizationLog->add((string)__($message->getText()), $logType);
        }
    }

    private function prepareFromTime(
        \DateTime $toTime
    ): \DateTime {
        $lastSynchronizationDate = $this->account->getOrdersLastSyncDate();

        $minDate = \M2E\Otto\Helper\Date::createCurrentGmt();
        $minDate->modify('-90 days');

        $sinceTime = \M2E\Otto\Helper\Date::createCurrentGmt();
        if ($lastSynchronizationDate !== null) {
            $sinceTime = $lastSynchronizationDate;

            if ($sinceTime < $minDate) {
                $sinceTime =  clone $minDate;
            }
        }

        if ($sinceTime >= $toTime) {
            $sinceTime = clone $toTime;
            $sinceTime->modify('- 5 minutes');
        }

        return $sinceTime;
    }

    private function updateLastOrderSynchronizationDate(
        \DateTime $toDate
    ): void {
        $this->account->setOrdersLastSyncDate(clone $toDate);

        $this->repository->save($this->account);
    }
}
