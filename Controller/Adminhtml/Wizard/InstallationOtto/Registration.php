<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class Registration extends Installation
{
    private \M2E\Otto\Model\Registration\UserInfo\Repository $manager;

    public function __construct(
        \M2E\Otto\Model\Registration\UserInfo\Repository $manager,
        \M2E\Otto\Helper\Magento $magentoHelper,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Otto\Helper\Module\License $licenseHelper
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseHelper,
        );
        $this->manager = $manager;
    }

    public function execute()
    {
        $this->init();

        return $this->registrationAction($this->manager);
    }
}
