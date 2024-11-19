<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class RemoveHandler
{
    private \M2E\Otto\Model\Product\DeleteService $productDeleteService;
    private \M2E\Otto\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Otto\Model\Product\DeleteService $productDeleteService,
        Repository $productRepository
    ) {
        $this->productDeleteService = $productDeleteService;
        $this->productRepository = $productRepository;
    }

    public function process(\M2E\Otto\Model\Product $listingProduct): void
    {
        if (!$listingProduct->isStatusNotListed()) {
            $listingProduct->setStatusNotListed(\M2E\Otto\Model\Product::STATUS_CHANGER_USER);

            $this->productRepository->save($listingProduct);
        }

        $this->productDeleteService->process($listingProduct);
    }
}
