<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Wizard;

class CompleteProcessor
{
    private \M2E\Otto\Model\Listing\AddProductsService $addProductsService;
    private \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\Otto\Model\Listing\Other\DeleteService $unmanagedProductDeleteService;
    private \M2E\Otto\Model\Magento\Product\CacheFactory $magentoProductFactory;

    public function __construct(
        \M2E\Otto\Model\Listing\AddProductsService $addProductsService,
        \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Otto\Model\Listing\Other\DeleteService $unmanagedProductDeleteService,
        \M2E\Otto\Model\Magento\Product\CacheFactory $magentoProductFactory
    ) {
        $this->addProductsService = $addProductsService;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    public function process(Manager $wizardManager): array
    {
        $listing = $wizardManager->getListing();

        $processedWizardProductIds = [];
        $listingProducts = [];
        foreach ($wizardManager->getNotProcessedProducts() as $wizardProduct) {
            $listingProduct = null;

            $processedWizardProductIds[] = $wizardProduct->getId();

            $magentoProduct = $this->magentoProductFactory->create()->setProductId($wizardProduct->getMagentoProductId());
            if (!$magentoProduct->exists()) {
                continue;
            }

            if ($wizardManager->isWizardTypeGeneral()) {
                $listingProduct = $this->addProductsService
                    ->addProduct(
                        $listing,
                        $magentoProduct,
                        $wizardProduct->getCategoryId(),
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );
            } elseif ($wizardManager->isWizardTypeUnmanaged()) {
                $unmanagedProduct = $this->listingOtherRepository->findById($wizardProduct->getUnmanagedProductId());
                if ($unmanagedProduct === null) {
                    continue;
                }

                if (!$unmanagedProduct->getMagentoProduct()->exists()) {
                    continue;
                }

                $listingProduct = $this->addProductsService
                    ->addFromUnmanaged(
                        $listing,
                        $unmanagedProduct,
                        $wizardProduct->getCategoryId(),
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );

                $this->unmanagedProductDeleteService->process($unmanagedProduct);
            }

            if ($listingProduct === null) {
                continue;
            }

            $listingProducts[] = $listingProduct;

            if (count($processedWizardProductIds) % 100 === 0) {
                $wizardManager->markProductsAsProcessed($processedWizardProductIds);
                $processedWizardProductIds = [];
            }
        }

        if (!empty($processedWizardProductIds)) {
            $wizardManager->markProductsAsProcessed($processedWizardProductIds);
        }

        return $listingProducts;
    }
}
