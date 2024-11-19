<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Template\Description;

use M2E\Otto\Controller\Adminhtml\Otto\Template\AbstractDescription;

class GetRandomMagentoProductId extends AbstractDescription
{
    private \M2E\Otto\Model\ResourceModel\Listing $listingResource;
    private \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Listing                   $listingResource,
        \Magento\Framework\HTTP\PhpEnvironment\Request                $phpEnvironmentRequest,
        \Magento\Catalog\Model\Product                                $productModel,
        \M2E\Otto\Model\Otto\Template\Manager             $templateManager
    ) {
        $this->listingResource = $listingResource;
        parent::__construct(
            $phpEnvironmentRequest,
            $productModel,
            $templateManager
        );
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getPost('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $productId = $this->getProductIdFromListing($storeId) ?? $this->getProductIdFromMagento();

        if ($productId) {
            $this->setJsonContent([
                'success' => true,
                'product_id' => $productId,
            ]);
        } else {
            $this->setJsonContent([
                'success' => false,
                'message' => __('You don\'t have any products in Magento catalog.'),
            ]);
        }

        return $this->getResult();
    }

    private function getProductIdFromListing($storeId): ?int
    {
        $listingProductCollection = $this->listingProductCollectionFactory->create();
        $collectionSize = $listingProductCollection->getSize();

        if ($collectionSize == 0) {
            return null;
        }

        $offset = rand(0, $collectionSize - 1);
        $listingProductCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(['id', 'magento_product_id'])
            ->joinLeft(
                ['ml' => $this->listingResource->getMainTable()],
                '`ml`.`id` = `main_table`.`listing_id`',
                ['store_id']
            )
            ->limit(1, $offset);

        /** @var \M2E\Otto\Model\Product $listingProduct */
        $listingProduct = $listingProductCollection
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem();

        return $listingProduct->getId();
    }

    private function getProductIdFromMagento(): ?int
    {
        $productCollection = $this->productModel->getCollection();
        $collectionSize = $productCollection->getSize();

        if ($collectionSize == 0) {
            return null;
        }

        $offset = rand(0, $collectionSize - 1);
        $productCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('entity_id')
            ->limit(1, $offset);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productCollection->getFirstItem();

        return $product->getId();
    }
}
