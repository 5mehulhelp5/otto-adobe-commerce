<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Product\Grid;

class Unmanaged extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractListing
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Model\Listing\Wizard\Repository $wizardRepository;
    private \M2E\Otto\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage;
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Listing\Wizard\Repository $wizardRepository
    ) {
        parent::__construct();

        $this->wizardRepository = $wizardRepository;
        $this->uiAccountRuntimeStorage = $uiAccountRuntimeStorage;
        $this->accountRepository = $accountRepository;
    }
    public function execute()
    {
        $wizard = $this->wizardRepository->findNotCompletedWizardByType(\M2E\Otto\Model\Listing\Wizard::TYPE_UNMANAGED);

        if (null !== $wizard) {
            $this->getMessageManager()->addNoticeMessage(
                __(
                    'Please make sure you finish adding new Products before moving to the next step.',
                ),
            );

            return $this->redirectToIndex($wizard->getId());
        }

        try {
            $this->loadAccount();
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->_redirect('*/otto_listing/index');
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('All Unmanaged Items'));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/unmanaged-listings-on-m2e-otto');

        return $this->getResult();
    }

    private function loadAccount(): void
    {
        $accountId = $this->getRequest()->getParam('account');
        if (empty($accountId)) {
            $account = $this->accountRepository->getFirst();
        } else {
            $account = $this->accountRepository->get((int)$accountId);
        }

        $this->uiAccountRuntimeStorage->setAccount($account);
    }
}
