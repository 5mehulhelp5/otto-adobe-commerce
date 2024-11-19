<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class Edit extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractListing
{
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::listings_items');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\Otto\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError($exception->getMessage());

            return $this->_redirect('*/otto_listing/index');
        }

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Listing\Edit::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(
            __('Edit M2E Otto Listing "%listing_title" Settings', ['listing_title' => $listing->getTitle()]),
        );

        return $this->getResult();
    }
}
