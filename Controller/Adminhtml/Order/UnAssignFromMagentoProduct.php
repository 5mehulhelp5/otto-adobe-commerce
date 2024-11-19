<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Order;

class UnAssignFromMagentoProduct extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    private \M2E\Otto\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\Otto\Model\Order\Item\ProductAssignService $productAssignService;

    public function __construct(
        \M2E\Otto\Model\Order\Item\Repository $orderItemRepository,
        \M2E\Otto\Model\Order\Item\ProductAssignService $productAssignService,
        $context = null
    ) {
        parent::__construct($context);
        $this->orderItemRepository = $orderItemRepository;
        $this->productAssignService = $productAssignService;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids'));
        $orderItems = $this->orderItemRepository->findOrderItemsByIds($orderItemIds);

        if (empty($orderItems)) {
            $this->setJsonContent([
                'error' => __('Please specify Required Options.'),
            ]);

            return $this->getResult();
        }

        $this->productAssignService->unAssign($orderItems);

        $this->setJsonContent([
            'success' => __('Item was Unlinked.'),
        ]);

        return $this->getResult();
    }
}
