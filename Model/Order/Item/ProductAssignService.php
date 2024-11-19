<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Item;

class ProductAssignService
{
    private \M2E\Otto\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param \M2E\Otto\Model\Order\Item[] $orderItems
     * @param \Magento\Catalog\Model\Product $magentoProduct
     * @param int $initiator
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function assign(array $orderItems, \Magento\Catalog\Model\Product $magentoProduct, int $initiator): void
    {
        $orders = [];
        foreach ($orderItems as $orderItem) {
            $orderItem->setMagentoProductId((int)$magentoProduct->getId());
            $this->orderItemRepository->save($orderItem);

            if ($initiator === \M2E\Otto\Helper\Data::INITIATOR_EXTENSION) {
                return;
            }

            if (!isset($orders[$orderItem->getOrder()->getId()])) {
                $orderItem->getOrder()->getLogService()->setInitiator($initiator);
                $orderItem->getOrder()->addSuccessLog(
                    'Order Item "%title%" was Linked.',
                    [
                        'title' => $orderItem->getTitle(),
                    ]
                );
            }
            $orders[$orderItem->getOrder()->getId()] = true;
        }
    }

    /**
     * @param \M2E\Otto\Model\Order\Item[] $orderItems
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function unAssign(array $orderItems): void
    {
        $orders = [];
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getOrder()->getReserve()->isPlaced()) {
                $orderItem->getOrder()->getReserve()->cancel();
            }

            $orderItem->getOrder()->getLogService()->setInitiator(\M2E\Otto\Helper\Data::INITIATOR_USER);
            $orderItem->removeAssociatedWithMagentoProduct();

            $this->orderItemRepository->save($orderItem);

            if (!isset($orders[$orderItem->getOrder()->getId()])) {
                $orderItem->getOrder()->addSuccessLog(
                    'Item "%title%" was Unlinked.',
                    [
                        'title' => $orderItem->getTitle(),
                    ]
                );
            }
            $orders[$orderItem->getOrder()->getId()] = true;
        }
    }
}
