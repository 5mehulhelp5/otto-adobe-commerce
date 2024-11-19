<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

class UploadByUser extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'order/upload_by_user';

    private \M2E\Otto\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory;
    private \M2E\Otto\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor,
        \M2E\Otto\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory,
        \M2E\Otto\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory,
        \M2E\Otto\Model\Cron\Manager $cronManager,
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
        $this->orderCreatorFactory = $orderCreatorFactory;
        $this->uploadByUserManagerFactory = $uploadByUserManagerFactory;
        $this->accountRepository = $accountRepository;
        $this->receiveOrderProcessor = $receiveOrderProcessor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\Otto\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();
        $synchronizationLog->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $ordersCreator = $this->orderCreatorFactory->create($this->getSynchronizationLog());
        $ordersCreator->setValidateAccountCreateDate(false);

        foreach ($this->accountRepository->getAll() as $account) {
            $manager = $this->uploadByUserManagerFactory->create($account);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $toTime = $manager->getToDate() ?? \M2E\Otto\Helper\Date::createCurrentGmt();
                $fromTime = $manager->getCurrentFromDate() ?? $manager->getFromDate();

                $response = $this->receiveOrderProcessor->process(
                    $account,
                    $fromTime,
                    $toTime
                );

                $this->processResponseMessages($response->getMessageCollection());

                $responseMaxDate = clone $response->getToDate();

                $this->updateUploadRecord($manager, $responseMaxDate);

                if (empty($response->getOrders())) {
                    continue;
                }

                $processOttoOrders = $ordersCreator
                    ->processOttoOrders($account, $response->getOrders());

                $ordersCreator->processMagentoOrders($processOttoOrders);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Upload Orders By User" Action for Otto Account "%account" was completed with error.',
                    ['account' => $account->getTitle()],
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    private function processResponseMessages(
        \M2E\Otto\Model\Connector\Response\MessageCollection $messageCollection
    ): void {
        foreach ($messageCollection->getMessages() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError()
                ? \M2E\Otto\Model\Log\AbstractModel::TYPE_ERROR
                : \M2E\Otto\Model\Log\AbstractModel::TYPE_WARNING;

            $this
                ->getSynchronizationLog()
                ->add((string)\__($message->getText()), $logType);
        }
    }

    private function updateUploadRecord(UploadByUser\Manager $manager, \DateTime $responseMaxDate): void
    {
        $manager->setCurrentFromDate($responseMaxDate->format('Y-m-d H:i:s'));

        if ($manager->getCurrentFromDate()->getTimestamp() >= $manager->getToDate()->getTimestamp()) {
            $manager->clear();
        }
    }
}
