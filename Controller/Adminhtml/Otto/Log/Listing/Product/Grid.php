<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Log\Listing\Product;

use M2E\Otto\Block\Adminhtml\Log\Listing\View;

class Grid extends \M2E\Otto\Controller\Adminhtml\Otto\Log\AbstractListing
{
    private \M2E\Otto\Helper\Data\Session $sessionHelper;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $listingProductRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Helper\Data\Session $sessionHelper
    ) {
        parent::__construct();

        $this->sessionHelper = $sessionHelper;
        $this->listingRepository = $listingRepository;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function execute()
    {
        $listingId = (int)$this->getRequest()->getParam(
            \M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
            false
        );
        $listingProductId = (int)$this->getRequest()->getParam(
            \M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_PRODUCT_ID_FIELD,
            false
        );

        if ($listingId) {
            try {
                $listing = $this->listingRepository->get($listingId);
            } catch (\M2E\Otto\Model\Exception\Logic $exception) {
                $this->setJsonContent([
                    'status' => false,
                    'message' => __('Listing does not exist.'),
                ]);

                return $this->getResult();
            }
        } elseif ($listingProductId) {
            $listingProduct = $this->listingProductRepository->find($listingProductId);

            if ($listingProduct === null) {
                $this->setJsonContent([
                    'status' => false,
                    'message' => __('Listing Product does not exist.'),
                ]);

                return $this->getResult();
            }
        }

        $sessionViewMode = $this->sessionHelper->getValue(
            \M2E\Otto\Helper\View\Otto::NICK . '_log_listing_view_mode'
        );

        if ($sessionViewMode === null) {
            $sessionViewMode = View\Switcher::VIEW_MODE_SEPARATED;
        }

        $viewMode = $this->getRequest()->getParam(
            'view_mode',
            $sessionViewMode
        );

        if ($viewMode === View\Switcher::VIEW_MODE_GROUPED) {
            $gridClass = \M2E\Otto\Block\Adminhtml\Otto\Log\Listing\Product\View\Grouped\Grid::class;
        } else {
            $gridClass = \M2E\Otto\Block\Adminhtml\Otto\Log\Listing\Product\View\Separated\Grid::class;
        }

        $block = $this->getLayout()->createBlock($gridClass);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
