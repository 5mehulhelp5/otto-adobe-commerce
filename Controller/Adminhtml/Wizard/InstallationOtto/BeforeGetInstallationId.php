<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class BeforeGetInstallationId extends Installation
{
    private \M2E\Otto\Helper\Module\Exception $helperException;
    private \M2E\Otto\Helper\Module\License $licenseHelper;
    private \M2E\Otto\Helper\View\Configuration $configurationHelper;
    private \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator;

    public function __construct(
        \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator,
        \M2E\Otto\Helper\Module\Exception $helperException,
        \M2E\Otto\Helper\Module\License $licenseHelper,
        \M2E\Otto\Helper\View\Configuration $configurationHelper,
        \M2E\Otto\Helper\Magento $magentoHelper,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseHelper,
        );

        $this->configurationHelper = $configurationHelper;
        $this->licenseHelper = $licenseHelper;
        $this->helperException = $helperException;
        $this->accessUrlGenerator = $accessUrlGenerator;
    }

    public function execute()
    {
        $title = $this->getRequest()->getParam('title');
        if (empty($title)) {
            $this->messageManager->addErrorMessage(__('Please enter a title.'));

            return $this->_redirect('*/*/installation');
        }

        $mode = \M2E\Otto\Model\Account::MODE_PRODUCTION;
        if ($this->getRequest()->getParam('mode') === \M2E\Otto\Model\Account::MODE_SANDBOX) {
            $mode = \M2E\Otto\Model\Account::MODE_SANDBOX;
        }

        try {
            $redirectUrl = $this->accessUrlGenerator->getInstallUrlForCreateFromWizard($title, $mode);
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);

            if (
                !$this->licenseHelper->isValidDomain() ||
                !$this->licenseHelper->isValidIp()
            ) {
                $error = __(
                    'The Otto installation is currently unavailable.<br/>Reason: %error_message </br>Go to the <a href="%url" target="_blank">License Page</a>.',
                    [
                        'error_message' => $exception->getMessage(),
                        'url' => $this->configurationHelper->getLicenseUrl(['wizard' => 1]),
                    ],
                );
            } else {
                $error = __(
                    'The Otto installation is currently unavailable.<br/>Reason: %error_message',
                    ['error_message' => $exception->getMessage()]
                );
            }

            $this->setJsonContent([
                'type' => 'error',
                'message' => $error,
            ]);

            return $this->getResult();
        }

        return $this->_redirect($redirectUrl);
    }
}
