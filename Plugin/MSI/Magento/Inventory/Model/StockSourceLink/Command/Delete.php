<?php

namespace M2E\Otto\Plugin\MSI\Magento\Inventory\Model\StockSourceLink\Command;

class Delete extends \M2E\Otto\Plugin\AbstractPlugin
{
    /** @var \M2E\Otto\Model\MSI\AffectedProducts */
    private $msiAffectedProducts;

    /** @var \M2E\Otto\PublicServices\Product\SqlChange */
    private $publicService;

    /** @var \Magento\InventoryApi\Api\StockRepositoryInterface */
    private $stockRepository;

    private \M2E\Otto\Model\Listing\LogService $listingLogService;
    private \M2E\Otto\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Listing\LogService $listingLogService,
        \M2E\Otto\Model\MSI\AffectedProducts $msiAffectedProducts,
        \M2E\Otto\PublicServices\Product\SqlChange $publicService,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->publicService = $publicService;
        $this->stockRepository = $objectManager->get(\Magento\InventoryApi\Api\StockRepositoryInterface::class);
        $this->listingLogService = $listingLogService;
        $this->productRepository = $productRepository;
    }

    //########################################

    /**
     * @param $interceptor
     * @param \Closure $callback
     * @param array ...$arguments
     *
     * @return mixed
     */
    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    /**
     * @param $interceptor
     * @param $result
     * @param array ...$arguments
     *
     * @return mixed
     */
    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\Inventory\Model\StockSourceLink[] $stockSourceLinks */
        $stockSourceLinks = $arguments[0];
        $stockId = reset($stockSourceLinks)->getStockId();

        $afterSources = [];
        foreach ($stockSourceLinks as $link) {
            $afterSources[] = $link->getSourceCode();
        }

        $result = $callback(...$arguments);

        if (empty($afterSources)) {
            return $result;
        }

        foreach ($this->msiAffectedProducts->getAffectedListingsByStock($stockId) as $listing) {
            foreach ($this->productRepository->findMagentoProductIdsByListingId($listing->getId()) as $productId) {
                $this->publicService->markQtyWasChanged($productId);
            }
            $this->logListingMessage($listing, $this->stockRepository->get($stockId));
        }
        $this->publicService->applyChanges();

        return $result;
    }

    //########################################

    private function logListingMessage(
        \M2E\Otto\Model\Listing $listing,
        \Magento\InventoryApi\Api\Data\StockInterface $stock
    ) {
        $this->listingLogService->addListing(
            $listing,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            \M2E\Otto\Model\Listing\Log::ACTION_UNKNOWN,
            null,
            \M2E\Otto\Helper\Module\Log::encodeDescription(
                'Source set was changed in the "%stock%" Stock used for M2E Otto Listing.',
                ['!stock' => $stock->getName()]
            ),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO
        );
    }
}
