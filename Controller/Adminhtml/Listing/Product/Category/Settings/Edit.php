<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Product\Category\Settings;

class Edit extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractListing
{
    private \M2E\Otto\Model\ResourceModel\Product $listingProductResource;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product $listingProductResource,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Otto\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();

        $this->listingProductResource = $listingProductResource;
        $this->categoryRepository = $categoryRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        /** @var string[] $listingProductId */
        $listingProductIds = $this->getRequestIds('products_id');
        if (empty($listingProductIds)) {
            return $this->getFailAjaxResult('Invalid product id(s)');
        }

        $listing = $this->listingRepository->find((int)$this->getRequest()->getParam('id'));
        if ($listing === null) {
            return $this->getFailAjaxResult('Listing not found');
        }

        $this->uiListingRuntimeStorage->setListing($listing);

        $ids = $this->listingProductResource
            ->getTemplateCategoryIds($listingProductIds, 'template_category_id', true);

        $categories = $this->categoryRepository->getItems($ids);

        /** @var ?\M2E\Otto\Model\Category $entity */
        $category = count($categories) === 1 ? reset($categories) : null;

        /** @var \M2E\Otto\Block\Adminhtml\Category\CategoryChooser $block */
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Category\CategoryChooser::class,
            '',
            ['selectedCategory' => $category !== null ? $category->getId() : null]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }

    private function getFailAjaxResult(string $message): \Magento\Framework\Controller\Result\Raw
    {
        $this->setJsonContent([
            'result' => false,
            'message' => $message,
        ]);

        return $this->getResult();
    }
}
