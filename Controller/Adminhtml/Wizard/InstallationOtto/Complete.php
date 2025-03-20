<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

use M2E\Otto\Helper\Module\Wizard;

class Complete extends Installation
{
    private \M2E\Core\Helper\Magento $magentoHelper;

    public function __construct(
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseService,
        );

        $this->magentoHelper = $magentoHelper;
    }

    public function execute()
    {
        $this->magentoHelper->clearMenuCache();

        $status = $this->getRequest()->getParam('status');

        if ($status === \M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto\Installation\ListingTutorial::INSTALLATION_COMPLETE) {
            $this->stepCompleteStatus();
        }

        if ($status === \M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto\Installation\ListingTutorial::INSTALLATION_SKIP) {
            $this->stepSkipStatus();
        }
    }

    private function stepCompleteStatus()
    {
        $this->setStatus(Wizard::STATUS_COMPLETED);
        $this->_redirect("*/otto_listing_create/index");
    }

    private function stepSkipStatus()
    {
        $this->setStatus(Wizard::STATUS_SKIPPED);
        $this->_redirect("*/otto_listing/index");
    }
}
