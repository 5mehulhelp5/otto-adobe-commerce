<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class Account extends Installation
{
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
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
        $this->accountRepository = $accountRepository;
    }

    public function execute()
    {
        $this->init();

        return $this->accountAction($this->accountRepository);
    }
}
