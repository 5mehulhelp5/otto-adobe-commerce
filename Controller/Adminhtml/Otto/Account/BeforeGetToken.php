<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Account;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractAccount;

class BeforeGetToken extends AbstractAccount
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
        $paramsAfterInstall = $this->accessUrlGenerator->createParamAfterInstall($this->getRequest());
        if ($paramsAfterInstall->getAccountId() === null && $paramsAfterInstall->getTitle() === null) {
            $this->messageManager->addErrorMessage(__('Please enter a title.'));
            return $this->_redirect('*/*/index');
        }

        $mode = \M2E\Otto\Model\Account::MODE_PRODUCTION;
        if ($this->getRequest()->getParam('mode') === \M2E\Otto\Model\Account::MODE_SANDBOX) {
            $mode = \M2E\Otto\Model\Account::MODE_SANDBOX;
        }

        try {
            $redirectUrl = $paramsAfterInstall->getAccountId() === null
                ? $this->accessUrlGenerator->getGrantAccessUrlForCreate($paramsAfterInstall->getTitle(), $mode)
                : $this->accessUrlGenerator->getGrantAccessUrlForUpdate($paramsAfterInstall->getAccountId(), $mode);
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);
            $error = __(
                'The Otto token obtaining is currently unavailable.<br/>Reason: %error_message',
                ['error_message' => $exception->getMessage()]
            );

            $this->messageManager->addError($error);

            return $this->getResult();
        }

        if ($this->getRequest()->getParam('extension') !== null) {
            $this->setJsonContent(
                [
                    'result' => true,
                    'redirectUrl' => $redirectUrl
                ]
            );

            return $this->getResult();
        }

        return $this->_redirect($redirectUrl);
    }
}
