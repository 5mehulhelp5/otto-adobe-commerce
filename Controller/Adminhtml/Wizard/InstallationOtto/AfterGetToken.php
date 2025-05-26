<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class AfterGetToken extends Installation
{
    private \M2E\Otto\Helper\Module\Exception $helperException;
    private \M2E\Otto\Model\Account\Create $accountCreate;

    public function __construct(
        \M2E\Otto\Model\Account\Create $accountCreate,
        \M2E\Otto\Helper\Module\Exception $helperException,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \M2E\Core\Helper\Magento $magentoHelper
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseService,
        );

        $this->helperException = $helperException;
        $this->accountCreate = $accountCreate;
    }

    public function execute()
    {
        $authCode = $this->getRequest()->getParam('code');
        $title = $this->getRequest()->getParam('title');

        $mode = \M2E\Otto\Model\Account::MODE_PRODUCTION;
        if ($this->getRequest()->getParam('mode') === \M2E\Otto\Model\Account::MODE_SANDBOX) {
            $mode = \M2E\Otto\Model\Account::MODE_SANDBOX;
        }

        if (!$authCode) {
            $this->messageManager->addError(__('Auth Code is not defined'));

            return $this->_redirect('*/*/installation');
        }

        try {
            $this->accountCreate->create($title, $authCode, $mode);

            $this->setStep($this->getNextStep());

            return $this->_redirect('*/*/installation');
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);

            $this->messageManager->addError(
                __(
                    'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'error_message' => $exception->getMessage()
                    ],
                ),
            );

            return $this->_redirect('*/*/installation');
        }
    }
}
