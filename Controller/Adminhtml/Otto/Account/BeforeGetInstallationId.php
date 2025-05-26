<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Account;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractAccount;

class BeforeGetInstallationId extends AbstractAccount
{
    private \M2E\Otto\Helper\Module\Exception $helperException;
    private \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator;

    public function __construct(
        \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator,
        \M2E\Otto\Helper\Module\Exception $helperException
    ) {
        parent::__construct();

        $this->helperException = $helperException;
        $this->accessUrlGenerator = $accessUrlGenerator;
    }

    public function execute()
    {
        $title = $this->getRequest()->getParam('title');
        if (empty($title)) {
            $this->messageManager->addErrorMessage(__('Please enter a title.'));
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->getUrl('*/*/index')
                ]
            );

            return $this->getResult();
        }

        $mode = \M2E\Otto\Model\Account::MODE_PRODUCTION;
        if ($this->getRequest()->getParam('mode') === \M2E\Otto\Model\Account::MODE_SANDBOX) {
            $mode = \M2E\Otto\Model\Account::MODE_SANDBOX;
        }

        try {
            $redirectUrl = $this->accessUrlGenerator->getInstallUrlForCreate($title, $mode);
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);
            $error = __(
                'The %channel_title installation is currently unavailable.<br/>Reason: %error_message',
                [
                    'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                    'error_message' => $exception->getMessage()
                ]
            );

            $this->messageManager->addError($error);
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->getUrl('*/*/index')
                ]
            );

            return $this->getResult();
        }

        $this->setJsonContent(
            [
                'result' => true,
                'redirectUrl' => $redirectUrl
            ]
        );

        return $this->getResult();
    }
}
