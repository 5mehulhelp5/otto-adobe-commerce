<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Account;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractAccount;

class Edit extends AbstractAccount
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        parent::__construct();

        $this->serverClient = $serverClient;
        $this->accountRepository = $accountRepository;
    }

    protected function getLayoutType(): string
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        $account = $this->accountRepository->find((int)$id);

        if ($account === null && $id) {
            $this->messageManager->addError(__('Account does not exist.'));

            return $this->_redirect('*/otto_account');
        }

        if ($account !== null) {
            $this->addLicenseMessage($account);
        }

        $headerText = __('Edit Account');
        $headerText .= ' "' . \M2E\Otto\Helper\Data::escapeHtml($account->getTitle()) . '"';

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend($headerText);

        /** @var \M2E\Otto\Block\Adminhtml\Otto\Account\Edit\Tabs $tabsBlock */
        $tabsBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Account\Edit\Tabs::class, '', [
                'account' => $account,
            ]);
        $this->addLeft($tabsBlock);

        /** @var \M2E\Otto\Block\Adminhtml\Otto\Account\Edit $contentBlock */
        $contentBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Account\Edit::class, '', [
                'account' => $account,
            ]);
        $this->addContent($contentBlock);

        return $this->getResultPage();
    }

    private function addLicenseMessage(\M2E\Otto\Model\Account $account): void
    {
        try {
            $command = new \M2E\Otto\Model\Otto\Connector\Account\Get\InfoCommand(
                $account->getServerHash(),
            );
            /** @var \M2E\Otto\Model\Otto\Connector\Account\Get\Status $status */
            $status = $this->serverClient->process($command);
        } catch (\Throwable $e) {
            return;
        }

        if ($status->isActive()) {
            return;
        }

        $this->addExtendedErrorMessage(
            __(
                'Work with this Account is currently unavailable for the following reason: <br/> %error_message',
                ['error_message' => $status->getNote()],
            ),
        );
    }
}
