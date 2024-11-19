<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

use M2E\Otto\Model\Product;

class AddProductsService
{
    private Product\Repository $listingProductRepository;
    private \M2E\Otto\Model\InstructionService $instructionService;
    private \M2E\Otto\Model\ProductFactory $listingProductFactory;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;
    private \M2E\Otto\Model\Listing\Other\Repository $unmanagedProductRepository;
    private \M2E\Otto\Model\Magento\Product\CacheFactory $magentoProductFactory;

    public function __construct(
        Product\Repository $listingProductRepository,
        \M2E\Otto\Model\InstructionService $instructionService,
        \M2E\Otto\Model\ProductFactory $listingProductFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService,
        \M2E\Otto\Model\Listing\Other\Repository $unmanagedProductRepository,
        \M2E\Otto\Model\Magento\Product\CacheFactory $magentoProductFactory
    ) {
        $this->listingProductRepository = $listingProductRepository;
        $this->instructionService = $instructionService;
        $this->listingProductFactory = $listingProductFactory;
        $this->listingLogService = $listingLogService;
        $this->unmanagedProductRepository = $unmanagedProductRepository;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    public function addProduct(
        \M2E\Otto\Model\Listing $listing,
        int $magentoProductId,
        int $categoryId,
        int $initiator = \M2E\Otto\Helper\Data::INITIATOR_UNKNOWN,
        ?\M2E\Otto\Model\Listing\Other $unmanagedProduct = null
    ): ?Product {
        $this->checkSupportedMagentoType($magentoProductId);

        $listingProduct = $this->findExistProduct($listing, $magentoProductId);
        if ($listingProduct === null) {
            $listingProduct = $this->listingProductFactory->create();
            $listingProduct->init(
                $listing->getId(),
                $magentoProductId,
                $categoryId
            );

            if ($unmanagedProduct !== null) {
                $listingProduct->fillFromUnmanagedProduct($unmanagedProduct);
            }

            $this->listingProductRepository->create($listingProduct);
        }

        $logMessage = (string)__('Product was Added');
        $logAction = \M2E\Otto\Model\Listing\Log::ACTION_ADD_PRODUCT_TO_LISTING;

        if (!empty($unmanagedProduct)) {
            $logMessage = (string)__('Item was Moved');
            $logAction = \M2E\Otto\Model\Listing\Log::ACTION_MOVE_FROM_OTHER_LISTING;
        }

        // Add message for listing log
        // ---------------------------------------
        $this->listingLogService->addProduct(
            $listingProduct,
            $initiator,
            $logAction,
            null,
            $logMessage,
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );
        // ---------------------------------------

        $this->instructionService->create(
            (int)$listingProduct->getId(),
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_ADDED,
            \M2E\Otto\Model\Listing::INSTRUCTION_INITIATOR_ADDING_PRODUCT,
            70,
        );

        return $listingProduct;
    }

    public function addFromUnmanaged(
        \M2E\Otto\Model\Listing $listing,
        \M2E\Otto\Model\Listing\Other $unmanagedProduct,
        int $categoryId,
        int $initiator
    ): ?Product {
        if (!$unmanagedProduct->getMagentoProductId()) {
            return null;
        }

        if (
            $listing->getAccount()->getId() !== $unmanagedProduct->getAccount()->getId()
        ) {
            return null;
        }

        $magentoProductId = $unmanagedProduct->getMagentoProductId();

        $listingProduct = $this->addProduct(
            $listing,
            $magentoProductId,
            $categoryId,
            $initiator,
            $unmanagedProduct
        );
        if ($listingProduct === null) {
            return null;
        }

        $unmanagedProduct->setMovedToListingProductId($listingProduct->getId());
        $this->unmanagedProductRepository->save($unmanagedProduct);

        $this->instructionService->create(
            $listingProduct->getId(),
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
            20,
        );

        return $listingProduct;
    }

    /**
     * @param \M2E\Otto\Model\Product $listingProduct
     * @param \M2E\Otto\Model\Listing $targetListing
     * @param \M2E\Otto\Model\Listing $sourceListing
     *
     * @return bool
     * @throws \Exception
     */
    public function addProductFromListing(
        \M2E\Otto\Model\Product $listingProduct,
        \M2E\Otto\Model\Listing $targetListing,
        \M2E\Otto\Model\Listing $sourceListing
    ): bool {
        if ($this->findExistProduct($targetListing, $listingProduct->getMagentoProductId()) !== null) {
            $this->listingLogService->addProduct(
                $listingProduct,
                \M2E\Otto\Helper\Data::INITIATOR_USER,
                \M2E\Otto\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
                null,
                (string)__('The Product was not moved because it already exists in the selected Listing'),
                \M2E\Otto\Model\Log\AbstractModel::TYPE_ERROR,
            );

            return false;
        }

        $listingProduct->changeListing($targetListing);
        $this->listingProductRepository->save($listingProduct);

        $logMessage = (string)__(
            'Item was moved from Listing %previous_listing_name.',
            [
                'previous_listing_name' => $sourceListing->getTitle()
            ],
        );

        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Otto\Helper\Data::INITIATOR_USER,
            \M2E\Otto\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
            null,
            $logMessage,
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );

        $logMessage = (string)__(
            'Product %product_title was moved to Listing %current_listing_name',
            [
                'product_title' => $listingProduct->getMagentoProduct()->getName(),
                'current_listing_name' => $targetListing->getTitle(),
            ],
        );

        $this->listingLogService->addListing(
            $sourceListing,
            \M2E\Otto\Helper\Data::INITIATOR_USER,
            \M2E\Otto\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
            null,
            $logMessage,
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );

        $this->instructionService->create(
            $listingProduct->getId(),
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_LISTING,
            20
        );

        return true;
    }

    private function findExistProduct(\M2E\Otto\Model\Listing $listing, int $magentoProductId): ?\M2E\Otto\Model\Product
    {
        return $this->listingProductRepository->findByListingAndMagentoProductId($listing, $magentoProductId);
    }

    private function isSupportedMagentoProductType(\M2E\Otto\Model\Magento\Product\Cache $ourMagentoProduct): bool
    {
        return $ourMagentoProduct->isSimpleType();
    }

    private function checkSupportedMagentoType(int $magentoProductId): void
    {
        $ourMagentoProduct = $this->magentoProductFactory->create()->setProductId($magentoProductId);
        if (!$this->isSupportedMagentoProductType($ourMagentoProduct)) {
            throw new \M2E\Otto\Model\Exception\Logic(
                (string)__(
                    'Unsupported magento product type - %typeId',
                    ['typeId' => $ourMagentoProduct->getTypeId()]
                ),
            );
        }
    }
}
