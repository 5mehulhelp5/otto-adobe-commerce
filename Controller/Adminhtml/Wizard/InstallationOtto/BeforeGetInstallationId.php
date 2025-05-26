<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class BeforeGetInstallationId extends Installation
{
    private \M2E\Otto\Helper\Module\Exception $helperException;
    private \M2E\Core\Model\LicenseService $licenseService;
    private \M2E\Otto\Helper\View\Configuration $configurationHelper;
    private \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator;

    public function __construct(
        \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator,
        \M2E\Otto\Helper\Module\Exception $helperException,
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\Otto\Helper\View\Configuration $configurationHelper,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseService,
        );

        $this->configurationHelper = $configurationHelper;
        $this->licenseService = $licenseService;
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
                !$this->licenseService->get()->getInfo()->getDomainIdentifier()->isValid()
                || !$this->licenseService->get()->getInfo()->getIpIdentifier()->isValid()
            ) {
                $error = __(
                    'The %channel_title installation is currently unavailable.<br/>Reason: %error_message </br>Go to the <a href="%url" target="_blank">License Page</a>.',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'error_message' => $exception->getMessage(),
                        'url' => $this->configurationHelper->getLicenseUrl(['wizard' => 1]),
                    ],
                );
            } else {
                $error = __(
                    'The %channel_title installation is currently unavailable.<br/>Reason: %error_message',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'error_message' => $exception->getMessage()
                    ]
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
