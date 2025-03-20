<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

class RemoveDeletedProduct
{
    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\StopQueue\CreateService $stopQueueCreateService;
    private \M2E\Otto\Model\Product\DeleteService $productDeleteService;
    /** @var \M2E\Otto\Model\Listing\LogService */
    private LogService $listingLogService;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\StopQueue\CreateService $stopQueueCreateService,
        \M2E\Otto\Model\Product\DeleteService $productDeleteService,
        LogService $listingLogService
    ) {
        $this->productRepository = $productRepository;
        $this->stopQueueCreateService = $stopQueueCreateService;
        $this->productDeleteService = $productDeleteService;
        $this->listingLogService = $listingLogService;
    }

    /**
     * @param \Magento\Catalog\Model\Product|int $magentoProduct
     *
     * @return void
     */
    public function process($magentoProduct): void
    {
        $magentoProductId = $magentoProduct instanceof \Magento\Catalog\Model\Product
            ? (int)$magentoProduct->getId()
            : (int)$magentoProduct;

        $listingsProducts = $this->productRepository->findByMagentoProductId($magentoProductId);

        $processedListings = [];
        foreach ($listingsProducts as $listingProduct) {
            $message = (string)__('Item was deleted from Magento.');
            if (!$listingProduct->isStatusNotListed()) {
                $message = (string)__('Item was deleted from Magento and stopped on the Channel.');
            }

            if ($listingProduct->isStoppable()) {
                $this->stopQueueCreateService->create($listingProduct);
            }

            $listingProduct->setStatusInactive(\M2E\Otto\Model\Product::STATUS_CHANGER_USER);
            $this->productRepository->save($listingProduct);

            $this->productDeleteService->process($listingProduct);

            $listingId = $listingProduct->getListingId();
            if (isset($processedListings[$listingId])) {
                continue;
            }

            $processedListings[$listingId] = true;

            $this->listingLogService->addProduct(
                $listingProduct,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\Otto\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                null,
                $message,
                \M2E\Otto\Model\Log\AbstractModel::TYPE_WARNING,
            );
        }
    }
}
