<?php

namespace M2E\Otto\Observer\StockItem\Save;

class After extends \M2E\Otto\Observer\StockItem\AbstractStockItem
{
    private \M2E\Otto\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;
    private ?int $magentoProductId = null;
    private array $affectedListingsParentProducts = [];
    private array $affectedListingsProducts = [];
    private \M2E\Otto\Model\Product\Repository $listingProductRepository;
    private \M2E\Otto\Model\ResourceModel\Product $listingProductResource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product $listingProductResource,
        \M2E\Otto\Model\Product\Repository $listingProductRepository,
        \M2E\Otto\Model\Listing\LogService $listingLogService,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\Model\Magento\Product\ChangeAttributeTrackerFactory $changeProcessorFactory
    ) {
        parent::__construct($registry, $stockItemFactory, $helperFactory);

        $this->changeAttributeTrackerFactory = $changeProcessorFactory;
        $this->listingLogService = $listingLogService;
        $this->listingProductRepository = $listingProductRepository;
        $this->listingProductResource = $listingProductResource;
    }

    public function beforeProcess(): void
    {
        parent::beforeProcess();

        $productId = (int)$this->getStockItem()->getProductId();

        if ($productId <= 0) {
            throw new \M2E\Otto\Model\Exception('Product ID should be greater than 0.');
        }

        $this->magentoProductId = $productId;

        $this->reloadStockItem();
    }

    protected function process(): void
    {
        if ($this->getStoredStockItem() === null) {
            return;
        }

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->addListingProductInstructions();

        $this->processQty();
        $this->processStockAvailability();
    }

    private function areThereAffectedItems(): bool
    {
        return !empty($this->getAffectedListingsProducts())
            || !empty($this->getAffectedListingsParentProducts());
    }

    private function addListingProductInstructions(): void
    {
        $listingProducts = array_merge(
            $this->getAffectedListingsProducts(),
            $this->getAffectedListingsParentProducts()
        );

        foreach ($listingProducts as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct,
                $listingProduct->getDescriptionTemplate()
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }

    private function processQty(): void
    {
        $oldValue = (int)$this->getStoredStockItem()->getOrigData('qty');
        $newValue = (int)$this->getStockItem()->getQty();

        if ($oldValue === $newValue) {
            return;
        }

        $listingProducts = array_merge(
            $this->getAffectedListingsProducts(),
            $this->getAffectedListingsParentProducts()
        );

        foreach ($listingProducts as $listingProduct) {
            /** @var \M2E\Otto\Model\Product $listingProduct */
            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Otto\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
                $oldValue,
                $newValue
            );
        }
    }

    private function processStockAvailability(): void
    {
        $oldValue = (bool)$this->getStoredStockItem()->getOrigData('is_in_stock');
        $newValue = (bool)$this->getStockItem()->getIsInStock();

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue === $newValue) {
            return;
        }

        $listingProducts = array_merge(
            $this->getAffectedListingsProducts(),
            $this->getAffectedListingsParentProducts()
        );

        foreach ($listingProducts as $listingProduct) {
            /** @var \M2E\Otto\Model\Product $listingProduct */
            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Otto\Model\Listing\Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                $oldValue,
                $newValue
            );
        }
    }

    /**
     * @return \M2E\Otto\Model\Product[]
     */
    private function getAffectedListingsProducts(): array
    {
        if (!empty($this->affectedListingsProducts)) {
            return $this->affectedListingsProducts;
        }

        return $this->affectedListingsProducts = $this->listingProductRepository
            ->getItemsByMagentoProductId($this->getProductId());
    }

    private function getAffectedListingsParentProducts(): array
    {
        if (!empty($this->affectedListingsParentProducts)) {
            return $this->affectedListingsParentProducts;
        }

        $listingProduct = $this->listingProductResource;
        $parentIds = $listingProduct->getParentEntityIdsByChild($this->getProductId());

        $affectedListingsParentProducts = [];
        foreach ($parentIds as $id) {
            $listingsParentProducts = $this->listingProductRepository->getItemsByMagentoProductId((int)$id);
            $affectedListingsParentProducts = array_merge($affectedListingsParentProducts, $listingsParentProducts);
        }

        return $this->affectedListingsParentProducts = $affectedListingsParentProducts;
    }

    private function getProductId(): int
    {
        return (int)$this->magentoProductId;
    }

    private function logListingProductMessage(
        \M2E\Otto\Model\Product $listingProduct,
        $action,
        $oldValue,
        $newValue
    ): void {
        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Otto\Helper\Data::INITIATOR_EXTENSION,
            $action,
            null,
            \M2E\Otto\Helper\Module\Log::encodeDescription(
                'From [%from%] to [%to%].',
                ['!from' => $oldValue, '!to' => $newValue]
            ),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    private function getStoredStockItem(): ?\Magento\CatalogInventory\Api\Data\StockItemInterface
    {
        $key = $this->getStockItemId() . '_' . $this->getStoreId();

        return $this->getRegistry()->registry($key);
    }
}
