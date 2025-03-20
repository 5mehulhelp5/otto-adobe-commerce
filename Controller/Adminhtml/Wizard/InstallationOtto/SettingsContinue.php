<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class SettingsContinue extends Installation
{
    private \M2E\Otto\Helper\Component\Otto\Configuration $configuration;

    public function __construct(
        \M2E\Otto\Helper\Component\Otto\Configuration $configuration,
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
        $this->configuration = $configuration;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        $this->configuration->setConfigValues($params);
        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }
}
