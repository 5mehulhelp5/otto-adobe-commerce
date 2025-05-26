<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Log\Listing\Product;

class Index extends \M2E\Otto\Controller\Adminhtml\Otto\Log\AbstractListing
{
    private \Magento\Framework\Filter\FilterManager $filterManager;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $listingProductRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct();

        $this->filterManager = $filterManager;
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
            $listing = $this->listingRepository->find($listingId);

            if ($listing === null) {
                $this->getMessageManager()->addErrorMessage(__('Listing does not exist.'));

                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend(
                __(
                    '%extension_title Listing "%s" Log',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                        's' => $listing->getTitle(),
                    ]
                ),
            );
        } elseif ($listingProductId) {
            $listingProduct = $this->listingProductRepository->find($listingProductId);

            if ($listingProduct === null) {
                $this->getMessageManager()->addErrorMessage(__('Listing Product does not exist.'));

                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend(
                __(
                    '%extension_title Listing Product "%name" Log',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                        'name' => $this->filterManager->truncate($listingProduct->getMagentoProduct()->getName(), ['length' => 28]),
                    ]
                )
            );
        } else {
            $this->getResult()->getConfig()->getTitle()->prepend(__('Listings Logs & Events'));
        }

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Log\Listing\Product\View::class)
        );

        return $this->getResult();
    }
}
