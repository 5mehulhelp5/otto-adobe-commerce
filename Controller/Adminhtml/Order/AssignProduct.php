<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Order;

class AssignProduct extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    private \M2E\Otto\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory;
    private \M2E\Otto\Model\Order\Item\ProductAssignService $productAssignService;
    private \M2E\Otto\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\Order\Item\Repository $orderItemRepository,
        \M2E\Otto\Model\Order\Item\ProductAssignService $productAssignService,
        \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\Otto\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->orderItemRepository = $orderItemRepository;
        $this->productAssignService = $productAssignService;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $sku = $this->getRequest()->getParam('sku', false);
        $productId = $this->getRequest()->getParam('product_id', false);
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids'));

        if (($productId === false && $sku === false) || empty($orderItemIds)) {
            $this->setJsonContent([
                'error' => __('Please specify Required Options.'),
            ]);

            return $this->getResult();
        }

        $orderItems = $this->orderItemRepository->findOrderItemsByIds($orderItemIds);
        if (empty($orderItems)) {
            $this->setJsonContent([
                'error' => __('Please specify Required Options.'),
            ]);

            return $this->getResult();
        }

        $collection = $this->magentoProductCollectionFactory->create();
        $storeId = 0;
        foreach ($orderItems as $orderItem) {
            $storeId = $orderItem->getStoreId();
            $collection->setStoreId($storeId);
            $collection->addFieldToFilter('entity_id', (int)$productId);
            $collection->addFieldToFilter('sku', $sku);

            $productData = $collection->getSelect()->query()->fetch();

            if (!$productData) {
                $this->setJsonContent([
                    'error' => (string)__('Product does not exist.'),
                ]);

                return $this->getResult();
            }
        }

        /** @var \M2E\Otto\Model\Magento\Product $magentoProduct */
        $magentoProduct = $this->magentoProductFactory->create()->loadProduct($productId, $storeId);

        if (!$magentoProduct->exists()) {
            $this->setJsonContent([
                'error' => (string)__('Product does not exist.'),
            ]);

            return $this->getResult();
        }

        $this->productAssignService->assign(
            $orderItems,
            $magentoProduct->getProduct(),
            \M2E\Otto\Helper\Data::INITIATOR_USER
        );

        $this->setJsonContent([
            'success' => (string)__('Order Item was Linked.'),
        ]);

        return $this->getResult();
    }
}
