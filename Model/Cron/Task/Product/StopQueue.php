<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Product;

class StopQueue extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'listing/product/stop_queue';

    protected int $intervalInSeconds = 3600;

    private const MAX_PROCESSED_LIFETIME_HOURS_INTERVAL = 720;

    private const MAXIMUM_PRODUCTS_PER_REQUEST = 20;

    private \M2E\Otto\Model\StopQueue\Repository $repository;
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\Otto\Model\Cron\Manager $cronManager,
        \M2E\Otto\Model\Synchronization\LogService $syncLogger,
        \M2E\Otto\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Otto\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\Otto\Model\StopQueue\Repository $repository,
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $taskRepo,
            $resource,
        );

        $this->repository = $repository;
        $this->serverClient = $serverClient;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->deleteOldProcessedItems();

        $this->processItems();
    }

    private function deleteOldProcessedItems(): void
    {
        $borderDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $borderDate->modify('- ' . self::MAX_PROCESSED_LIFETIME_HOURS_INTERVAL . ' hours');

        $this->repository->deleteCompletedAfterBorderDate($borderDate);
    }

    private function processItems(): void
    {
        $processedItems = [];
        foreach ($this->repository->findNotProcessed(self::MAXIMUM_PRODUCTS_PER_REQUEST) as $item) {
            $requestData = $item->getRequestData();

            $uniqueProcessedItemKey
                = "{$requestData['account']}_{$requestData['otto_product_sku']}";
            if (isset($processedItems[$uniqueProcessedItemKey])) {
                continue;
            }

            $processedItems[$uniqueProcessedItemKey] = true;

            $command = new \M2E\Otto\Model\Otto\Connector\Item\DeleteCommand(
                $requestData['account'],
                [
                    'sku' => $requestData['otto_product_sku'],
                    'action_date' => $requestData['action_date']
                ]
            );

            /** @var \M2E\Core\Model\Connector\Response $response */
            $response = $this->serverClient->process($command);

            if (
                isset($response->getResponseData()['processing_id'])
                && !empty($response->getResponseData()['processing_id'])
            ) {
                $item->setAsProcessed();
                $this->repository->save($item);
            }
        }
    }
}
