<?php

declare(strict_types=1);

namespace M2E\Otto\Observer\Product;

class Delete extends AbstractProduct
{
    private \M2E\Otto\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct;
    private \M2E\Otto\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct;

    public function __construct(
        \M2E\Otto\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct,
        \M2E\Otto\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\Otto\Helper\Factory $helperFactory
    ) {
        parent::__construct(
            $productFactory,
            $ourMagentoProductFactory,
            $helperFactory
        );
        $this->unmanagedUnmapDeletedProduct = $unmanagedUnmapDeletedProduct;
        $this->listingRemoveDeletedProduct = $listingRemoveDeletedProduct;
    }

    public function process(): void
    {
        if (empty($this->getProductId())) {
            return;
        }

        $this->unmanagedUnmapDeletedProduct->process($this->getProduct());
        $this->listingRemoveDeletedProduct->process($this->getProduct());
    }
}
