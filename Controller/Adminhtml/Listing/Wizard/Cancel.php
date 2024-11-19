<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard;

class Cancel extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();

        $wizardManager = $this->wizardManagerFactory->createById($id);

        $wizardManager->cancel();

        if ($wizardManager->isWizardTypeGeneral()) {
            return $this->_redirect('*/otto_listing/view', ['id' => $wizardManager->getListing()->getId()]);
        }

        if ($wizardManager->isWizardTypeUnmanaged()) {
            return $this->_redirect('*/otto_listing_unmanaged/index');
        }

        return $this->_redirect('*/*/index');
    }
}
