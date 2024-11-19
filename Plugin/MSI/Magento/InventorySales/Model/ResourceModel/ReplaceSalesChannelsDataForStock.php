<?php

namespace M2E\Otto\Plugin\MSI\Magento\InventorySales\Model\ResourceModel;

use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

class ReplaceSalesChannelsDataForStock extends \M2E\Otto\Plugin\AbstractPlugin
{
    /** @var \M2E\Otto\Model\MSI\AffectedProducts */
    private $msiAffectedProducts;

    /** @var \M2E\Otto\PublicServices\Product\SqlChange */
    private $publicService;

    // ---------------------------------------

    /** @var StockRepositoryInterface */
    private $stockRepository;

    /** @var \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface */
    private $getAssignedChannels;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;
    private \M2E\Otto\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Listing\LogService $listingLogService,
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\Model\MSI\AffectedProducts $msiAffectedProducts,
        \M2E\Otto\PublicServices\Product\SqlChange $publicService,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($helperFactory);
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->publicService = $publicService;

        $this->stockRepository = $objectManager->get(StockRepositoryInterface::class);
        $this->getAssignedChannels = $objectManager->get(GetAssignedSalesChannelsForStockInterface::class);
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
     * @param \Closure $callback
     * @param array $arguments
     *
     * @return mixed
     */
    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        $stockId = $arguments[1];
        $channelsAfter = $arguments[0];
        $channelsBefore = $this->getAssignedChannels->execute($stockId);

        $result = $callback(...$arguments);

        /** @var \Magento\InventorySalesApi\Api\Data\SalesChannelInterface[] $addedChannels */
        $addedChannels = $this->getOnlyAddedChannels($channelsBefore, $channelsAfter);
        if (empty($addedChannels)) {
            return $result;
        }

        $stock = $this->stockRepository->get($stockId);

        foreach ($addedChannels as $addedChannel) {
            foreach ($this->msiAffectedProducts->getAffectedListingsByChannel($addedChannel->getCode()) as $listing) {
                foreach ($this->productRepository->findMagentoProductIdsByListingId($listing->getId()) as $prId) {
                    $this->publicService->markQtyWasChanged($prId);
                }
                $this->logListingMessage($listing, $addedChannel, $stock);
            }
        }
        $this->publicService->applyChanges();

        return $result;
    }

    /**
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface[] $oldChannels
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface[] $newChannels
     *
     * @return array
     */
    private function getOnlyAddedChannels(array $oldChannels, array $newChannels)
    {
        $oldCodes = [];

        foreach ($oldChannels as $oldChannel) {
            $oldCodes[] = $oldChannel->getCode();
        }

        return array_filter($newChannels, function ($channel) use ($oldCodes) {
            return !in_array($channel->getCode(), $oldCodes, true);
        });
    }

    //########################################

    private function logListingMessage(
        \M2E\Otto\Model\Listing $listing,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $channel,
        \Magento\InventoryApi\Api\Data\StockInterface $stock
    ): void {
        $this->listingLogService->addListing(
            $listing,
            \M2E\Otto\Helper\Data::INITIATOR_EXTENSION,
            \M2E\Otto\Model\Listing\Log::ACTION_UNKNOWN,
            null,
            \M2E\Otto\Helper\Module\Log::encodeDescription(
                'Website "%website%" has been linked with stock "%stock%".',
                ['!website' => $channel->getCode(), '!stock' => $stock->getName()]
            ),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
