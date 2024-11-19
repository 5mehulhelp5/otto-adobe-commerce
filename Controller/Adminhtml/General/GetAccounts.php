<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\General;

class GetAccounts extends \M2E\Otto\Controller\Adminhtml\AbstractGeneral
{
    private \M2E\Otto\Model\Account\Repository $accountsRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountsRepository
    ) {
        parent::__construct();

        $this->accountsRepository = $accountsRepository;
    }

    public function execute()
    {
        $accounts = [];
        foreach ($this->accountsRepository->getAll() as $account) {
            $accounts[] = [
                'id' => $account->getId(),
                'title' => \M2E\Otto\Helper\Data::escapeHtml($account->getTitle()),
            ];
        }

        $this->setJsonContent($accounts);

        return $this->getResult();
    }
}
