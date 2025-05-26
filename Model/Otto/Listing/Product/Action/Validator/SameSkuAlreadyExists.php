<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class SameSkuAlreadyExists implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    private \M2E\Otto\Model\Listing\Other\Repository $otherRepository;
    private \M2E\Otto\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Other\Repository $otherRepository,
        \M2E\Otto\Model\Product\Repository $productRepository
    ) {
        $this->otherRepository = $otherRepository;
        $this->productRepository = $productRepository;
    }

    public function validate(\M2E\Otto\Model\Product $product): ?ValidatorMessage
    {
        $id = $product->getId();

        $ottoProductSku = $product->getOttoProductSku();
        if (empty($ottoProductSku)) {
            $ottoProductSku = $product->getMagentoProduct()->getSku();
        }

        $existUnmanagedProduct = $this->otherRepository->findByProductSKUs(
            [$ottoProductSku],
            $product->getAccount()->getId(),
        );

        if (!empty($existUnmanagedProduct)) {
            return new ValidatorMessage(
                (string)__('Product with the same SKU already exists in Unmanaged Items.
                 Once the Item is mapped to a Magento Product, it can be moved to an M2E Listing.'),
                \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_DUPLICATE_SKU_IN_UNMANAGED
            );
        }

        $existProduct = $this->productRepository->findListedOrLockedProductsBySku($id, $ottoProductSku);

        if ($existProduct && ($existProduct->getId() !== $product->getId())) {
            return new ValidatorMessage(
                (string)__(
                    'Product with the same SKU already exists in your %listing_title Listing',
                    [
                        'listing_title' => $existProduct->getListing()->getTitle()
                    ]
                ),
                \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_DUPLICATE_SKU_IN_LISTING
            );
        }

        return null;
    }
}
