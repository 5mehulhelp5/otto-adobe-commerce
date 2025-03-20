<?php

namespace M2E\Otto\Model\Cron\Task\Order;

class ReserveCancel extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'order/reserve_cancel';

    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
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
        $this->accountRepository = $accountRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
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
        $permittedAccounts = $this->accountRepository->getAll();

        if (empty($permittedAccounts)) {
            return;
        }

        $this->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        foreach ($permittedAccounts as $account) {
            $this->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->processAccount($account);
            } catch (\Exception $exception) {
                $message = (string)__(
                    'The "Reserve Cancellation" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    private function processAccount(\M2E\Otto\Model\Account $account): void
    {
        foreach ($this->getOrdersForRelease($account) as $order) {
            /** @var \M2E\Otto\Model\Order $order */
            $order->getReserve()->release();
        }
    }

    private function getOrdersForRelease(\M2E\Otto\Model\Account $account): array
    {
        $collection = $this->orderCollectionFactory->create()
                                          ->addFieldToFilter('account_id', $account->getId())
                                          ->addFieldToFilter(
                                              'reservation_state',
                                              \M2E\Otto\Model\Order\Reserve::STATE_PLACED
                                          );

        $reservationDays = $account->getOrdersSettings()->getQtyReservationDays();

        $minReservationStartDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $minReservationStartDate->modify('- ' . $reservationDays . ' days');
        $minReservationStartDate = $minReservationStartDate->format('Y-m-d H:i');

        $collection->addFieldToFilter('reservation_start_date', ['lteq' => $minReservationStartDate]);

        return $collection->getItems();
    }
}
