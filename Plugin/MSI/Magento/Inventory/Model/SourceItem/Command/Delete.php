<?php

namespace M2E\Otto\Plugin\MSI\Magento\Inventory\Model\SourceItem\Command;

class Delete extends \M2E\Otto\Plugin\AbstractPlugin
{
    private \M2E\Otto\Model\MSI\AffectedProducts $msiAffectedProducts;
    private \M2E\Otto\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\Otto\Model\Listing\LogService $listingLogService,
        \M2E\Otto\Model\MSI\AffectedProducts $msiAffectedProducts,
        \M2E\Otto\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory
    ) {
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->listingLogService = $listingLogService;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    protected function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems */
        $sourceItems = $arguments[0];

        $result = $callback(...$arguments);

        foreach ($sourceItems as $sourceItem) {
            $affected = $this->msiAffectedProducts->getAffectedProductsBySourceAndSku(
                $sourceItem->getSourceCode(),
                $sourceItem->getSku()
            );

            if (empty($affected)) {
                continue;
            }

            $this->addListingProductInstructions($affected);

            foreach ($affected as $listingProduct) {
                $this->logListingProductMessage($listingProduct, $sourceItem);
            }
        }

        return $result;
    }

    private function logListingProductMessage(
        \M2E\Otto\Model\Product $listingProduct,
        \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItem
    ): void {
        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            \M2E\Otto\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
            null,
            \M2E\Otto\Helper\Module\Log::encodeDescription(
                'The "%source%" Source was unassigned from product.',
                ['!source' => $sourceItem->getSourceCode()]
            ),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    /**
     * @param \M2E\Otto\Model\Product[] $affectedProducts*
     */
    private function addListingProductInstructions(array $affectedProducts): void
    {
        foreach ($affectedProducts as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct,
                $listingProduct->getDescriptionTemplate()
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }
}
