<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard;

class Create extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Listing\Wizard\Create $createModel;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\Listing\Wizard\Create $createModel
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->createModel = $createModel;
    }

    public function execute()
    {
        $listingId = (int)$this->getRequest()->getParam('listing_id');
        $type = $this->getRequest()->getParam('type');
        if (empty($listingId) || empty($type)) {
            $this->getMessageManager()->addError(__('Cannot start Wizard, Listing ID must be provided first.'));

            return $this->_redirect('*/otto_listing/index');
        }

        $listing = $this->listingRepository->get($listingId);

        /** @var \M2E\Otto\Model\Listing\Wizard $wizard */
        $wizard = $this->createModel->process($listing, $type);

        return $this->redirectToIndex($wizard->getId());
    }
}
