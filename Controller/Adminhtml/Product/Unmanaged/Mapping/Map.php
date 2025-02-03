<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Product\Unmanaged\Mapping;

class Map extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    private \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\Otto\Model\Listing\Other\MappingService $mappingService;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Otto\Model\Listing\Other\MappingService $mappingService,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->mappingService = $mappingService;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->listingOtherRepository = $listingOtherRepository;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id'); // Magento
        $productOtherId = (int)$this->getRequest()->getParam('other_product_id');
        $accountId = (int)$this->getRequest()->getParam('account_id');

        if (!$productId || !$productOtherId) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);

        $magentoCatalogProductModel = $collection->getFirstItem();
        if ($magentoCatalogProductModel->isEmpty()) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        $productId = $magentoCatalogProductModel->getId();

        $listingOther = $this->listingOtherRepository->get($productOtherId);

        $this->mappingService->mapProduct($listingOther, (int)$productId);

        return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
    }
}
