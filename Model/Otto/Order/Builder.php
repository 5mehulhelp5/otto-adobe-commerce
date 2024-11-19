<?php

namespace M2E\Otto\Model\Otto\Order;

class Builder extends \Magento\Framework\DataObject
{
    public const STATUS_NOT_MODIFIED = 0;
    public const STATUS_NEW = 1;
    public const STATUS_UPDATED = 2;

    private \M2E\Otto\Model\Order $order;
    private int $status = self::STATUS_NOT_MODIFIED;
    private bool $isOrderStatusHasUpdated = false;
    private array $items = [];

    // ----------------------------------------

    private \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater;
    private \M2E\Otto\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \M2E\Otto\Model\OrderFactory $orderFactory;
    private \M2E\Otto\Model\Otto\Order\Item\BuilderFactory $orderItemBuilderFactory;
    private \M2E\Otto\Model\Account $account;
    private \M2E\Otto\Model\Otto\Order\ServerDataToOrderDataConverter $orderDataConverter;
    private \M2E\Otto\Block\Adminhtml\Otto\Order\StatusHelper $orderStatusHelper;
    private \M2E\Otto\Model\Order\Note\Create $noteCreateService;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Otto\Order\StatusHelper $orderStatusHelper,
        \M2E\Otto\Model\Otto\Order\ServerDataToOrderDataConverter $orderDataConverter,
        \M2E\Otto\Model\Otto\Order\Item\BuilderFactory $orderItemBuilderFactory,
        \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater,
        \M2E\Otto\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \M2E\Otto\Model\OrderFactory $orderFactory,
        \M2E\Otto\Model\Order\Note\Create $noteCreateService
    ) {
        parent::__construct();
        $this->noteCreateService = $noteCreateService;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->orderItemBuilderFactory = $orderItemBuilderFactory;
        $this->orderDataConverter = $orderDataConverter;
        $this->orderStatusHelper = $orderStatusHelper;
    }

    public function initialize(
        \M2E\Otto\Model\Account $account,
        array $data = []
    ): void {
        $this->account = $account;
        $this->initializeData($data);
        $this->initializeOrder();
    }

    private function initializeData(array $data): void
    {
        $this->setData($this->orderDataConverter->convert($data));
        $this->setData('account_id', $this->account->getId());

        // ---------------------------------------
        $this->items = $data['order_items'];
    }

    private function initializeOrder(): void
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrders = $this->getExistedOrders();

        // New order
        // ---------------------------------------
        if (empty($existOrders)) {
            $this->status = self::STATUS_NEW;
            $this->order = $this->orderFactory->create();

            return;
        }

        // ---------------------------------------

        // duplicated Otto orders. remove M2E Otto order without magento order id or newest order
        // ---------------------------------------
        if (count($existOrders) > 1) {
            $isDeleted = false;

            foreach ($existOrders as $key => $order) {
                $magentoOrderId = $order->getMagentoOrderId();
                if ($magentoOrderId !== null) {
                    continue;
                }

                $order->delete();
                unset($existOrders[$key]);
                $isDeleted = true;
                break;
            }

            if (!$isDeleted) {
                $orderForRemove = reset($existOrders);
                $orderForRemove->delete();
            }
        }

        // ---------------------------------------

        // Already exist order
        // ---------------------------------------
        $this->order = reset($existOrders);
        $this->status = self::STATUS_UPDATED;
        // ---------------------------------------
    }

    /**
     * @return \M2E\Otto\Model\Order[]
     */
    private function getExistedOrders(): array
    {
        $orderId = $this->getData('otto_order_id');

        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter('account_id', ['eq' => $this->account->getId()]);
        $collection->addFieldToFilter('otto_order_id', ['eq' => $orderId]);
        $collection->setOrder('id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);

        return $collection->getItems();
    }

    // ----------------------------------------

    public function process(): ?\M2E\Otto\Model\Order
    {
        if (!$this->canCreateOrUpdateOrder()) {
            return null;
        }

        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        if ($this->isNew()) {
            $this->processNew();
        }

        if ($this->isUpdated()) {
            $this->processOrderUpdates();
            $this->processMagentoOrderUpdates();
        }

        return $this->order;
    }

    // ----------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \Exception
     */
    protected function createOrUpdateItems()
    {
        $itemsCollection = $this->order->getItemsCollection();
        $itemsCollection->load();

        foreach ($this->items as $orderItemData) {
            $orderItemData['order_id'] = $this->order->getId();

            $itemBuilder = $this->orderItemBuilderFactory->create();
            $itemBuilder->initialize($orderItemData);

            $item = $itemBuilder->process();
            $item->setOrder($this->order);

            $itemsCollection->removeItemByKey($item->getId());
            $itemsCollection->addItem($item);
        }
    }

    // ---------------------------------------

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isUpdated(): bool
    {
        return $this->status === self::STATUS_UPDATED;
    }

    // ----------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \Exception
     */
    private function canCreateOrUpdateOrder(): bool
    {
        if ($this->order->getId()) {
            $newPurchaseUpdateDate = \M2E\Otto\Helper\Date::createDateGmt(
                $this->getData('purchase_update_date')
            );
            $oldPurchaseUpdateDate = \M2E\Otto\Helper\Date::createDateGmt(
                $this->order->getPurchaseUpdateDate()
            );

            if ($oldPurchaseUpdateDate > $newPurchaseUpdateDate) {
                return false;
            }
        }

        return true;
    }

    private function createOrUpdateOrder(): void
    {
        foreach ($this->getData() as $key => $value) {
            if (
                !$this->order->getId()
                || ($this->order->hasData($key) && $this->order->getData($key) != $value)
            ) {
                $this->order->addData($this->getData());

                $this->order->save();

                break;
            }
        }

        if ($this->isNew()) {
            $addition = trim($this->order->getShippingAdditionalInfo());
            if (empty($addition)) {
                return;
            }

            $note = (string)__(
                "<b>Additional Address Details:</b><br> %additional_info",
                ['additional_info' => $addition]
            );
            $this->noteCreateService->create($this->order, $note);
        }

        $this->order->setAccount($this->account);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    private function processNew(): void
    {
        if (!$this->isNew()) {
            return;
        }

        $ottoAccount = $this->account;

        if (
            $this->order->hasListingProductItems()
            && !$ottoAccount->getOrdersSettings()->isListingEnabled()
        ) {
            return;
        }

        if (
            $this->order->hasOtherListingItems()
            && !$ottoAccount->getOrdersSettings()->isUnmanagedListingEnabled()
        ) {
            return;
        }

        if (!$this->order->canCreateMagentoOrder()) {
            $this->order->addWarningLog(
                'Magento Order was not created. Reason: %msg%',
                [
                    'msg' => 'Order Creation Rules were not met. ' .
                        'Press Create Order Button at Order View Page to create it anyway.',
                ]
            );
        }
    }

    private function checkUpdates(): void
    {
        if (!$this->isUpdated()) {
            return;
        }

        if ($this->getData('order_status') !== $this->order->getOrderStatus()) {
            $this->isOrderStatusHasUpdated = true;
        }
    }

    private function hasUpdates(): bool
    {
        return $this->isOrderStatusHasUpdated;
    }

    private function processOrderUpdates(): void
    {
        if (!$this->hasUpdates()) {
            return;
        }

        $this->order->addSuccessLog(
            sprintf(
                'Order status was updated to %s on Otto',
                $this->orderStatusHelper->getStatusLabel($this->order->getOrderStatus())
            )
        );
    }

    private function processMagentoOrderUpdates(): void
    {
        if (!$this->hasUpdates()) {
            return;
        }

        $magentoOrder = $this->order->getMagentoOrder();
        if ($magentoOrder === null) {
            return;
        }

        $magentoOrderUpdater = $this->magentoOrderUpdater;
        $magentoOrderUpdater->setMagentoOrder($magentoOrder);

        $proxy = $this->order->getProxy();
        $proxy->setStore($this->order->getStore());

        $magentoOrderUpdater->finishUpdate();
    }
}
