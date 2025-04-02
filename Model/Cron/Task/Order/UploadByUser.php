<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

class UploadByUser implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/upload_by_user';

    private \M2E\Otto\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory;
    private \M2E\Otto\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor;
    private \M2E\Otto\Model\Synchronization\LogService $syncLog;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor,
        \M2E\Otto\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory,
        \M2E\Otto\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory
    ) {
        $this->orderCreatorFactory = $orderCreatorFactory;
        $this->uploadByUserManagerFactory = $uploadByUserManagerFactory;
        $this->accountRepository = $accountRepository;
        $this->receiveOrderProcessor = $receiveOrderProcessor;
    }

    /**
     * @param \M2E\Otto\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $this->syncLog = $context->getSynchronizationLog();
        $this->syncLog->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_ORDERS);

        $ordersCreator = $this->orderCreatorFactory->create($context->getSynchronizationLog());
        $ordersCreator->setValidateAccountCreateDate(false);

        foreach ($this->accountRepository->getAll() as $account) {
            $manager = $this->uploadByUserManagerFactory->create($account);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $toTime = $manager->getToDate() ?? \M2E\Core\Helper\Date::createCurrentGmt();
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

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
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

            $this->syncLog
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
