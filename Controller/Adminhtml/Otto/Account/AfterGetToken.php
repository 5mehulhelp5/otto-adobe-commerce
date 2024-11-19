<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Account;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractAccount;

class AfterGetToken extends AbstractAccount
{
    private \M2E\Otto\Helper\Module\Exception $helperException;
    private \M2E\Otto\Model\Account\Update $accountUpdate;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Account\Create $accountCreate;
    private \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator;

    public function __construct(
        \M2E\Otto\Model\Account\AccessUrlGenerator $accessUrlGenerator,
        \M2E\Otto\Model\Account\Create $accountCreate,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Account\Update $accountUpdate,
        \M2E\Otto\Helper\Module\Exception $helperException
    ) {
        parent::__construct();

        $this->helperException = $helperException;
        $this->accountUpdate = $accountUpdate;
        $this->accountRepository = $accountRepository;
        $this->accountCreate = $accountCreate;
        $this->accessUrlGenerator = $accessUrlGenerator;
    }

    // ----------------------------------------

    public function execute()
    {
        $accessParams = $this->accessUrlGenerator->createParamAfterGrantAccess($this->getRequest());

        if (empty($accessParams->getAuthCode())) {
            $this->_redirect('*/*/index');
        }

        try {
            if (!$accessParams->hasAccountId()) {
                $account = $this->accountCreate->create(
                    (string)$accessParams->getTitle(),
                    $accessParams->getAuthCode(),
                    $accessParams->getMode(),
                );

                return $this->_redirect(
                    '*/*/edit',
                    [
                        'id' => $account->getId()
                    ],
                );
            }

            $account = $this->accountRepository->find((int)$accessParams->getAccountId());
            if ($account === null) {
                throw new \LogicException('Account not found.');
            }

            try {
                $this->accountUpdate->updateCredentials($account, $accessParams->getAuthCode());
            } catch (\M2E\Otto\Model\Account\Exception\InstallNotFound $e) {
                return $this->_redirect(
                    $this->accessUrlGenerator->getInstallUrlForUpdate(
                        $account->getId(),
                        $account->getMode()
                    )
                );
            }

            $this->messageManager->addSuccessMessage(__('Auth code was saved'));

            return $this->_redirect('*/*/edit', ['id' => $account->getId()]);
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);

            $this->messageManager->addError(
                __(
                    'The Otto access obtaining is currently unavailable.<br/>Reason: %error_message',
                    ['error_message' => $exception->getMessage()],
                ),
            );

            return $this->_redirect('*/otto_account');
        }
    }
}
