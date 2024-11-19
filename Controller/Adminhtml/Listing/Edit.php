<?php

namespace M2E\Otto\Controller\Adminhtml\Listing;

use M2E\Otto\Controller\Adminhtml\AbstractListing;

class Edit extends AbstractListing
{
    /** @var \M2E\Otto\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Helper\Data\GlobalData $globalData,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['id'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listing = $this->listingRepository->get($params['id']);

        if ($this->getRequest()->isPost()) {
            $listing->addData($params)->save();

            return $this->getResult();
        }

        $this->globalData->setValue('edit_listing', $listing);

        $this->setAjaxContent(
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Listing\Edit::class)
        );

        return $this->getResult();
    }
}
