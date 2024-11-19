<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Unmanaged;

class ButtonsBlock extends \Magento\Backend\Block\Widget
{
    private \M2E\Otto\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage;
    private \M2E\Otto\Model\Listing\InventorySync\AccountLockManager $accountLockManager;
    private \M2E\Otto\Model\Listing\Other\Repository $unmanagedRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage,
        \M2E\Otto\Model\Listing\InventorySync\AccountLockManager $accountLockManager,
        \M2E\Otto\Model\Listing\Other\Repository $otherRepository,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        ?\Magento\Framework\Json\Helper\Data $jsonHelper = null,
        ?\Magento\Directory\Helper\Data $directoryHelper = null
    ) {
        $this->uiAccountRuntimeStorage = $uiAccountRuntimeStorage;
        $this->accountLockManager = $accountLockManager;
        $this->unmanagedRepository = $otherRepository;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('listing/unmanaged/buttons_block.phtml');
    }

    public function getUrlForOpenSettings(): string
    {
        return $this->getUrl(
            '*/otto_account/edit',
            ['id' => $this->getAccount()->getId(), 'close_on_save' => true, 'tab' => 'listingOther']
        );
    }

    public function getUrlForResetUnmanaged(): string
    {
        return $this->getUrl(
            '*/otto_listing_unmanaged/reset',
            ['account_id' => $this->getAccount()->getId()]
        );
    }

    public function isNeedShowOpenSettingsButton(): bool
    {
        return !$this->getAccount()->getUnmanagedListingSettings()->isSyncEnabled();
    }

    public function isNeedShowResetButton(): bool
    {
        return !$this->isDownloadUnmanagedInProcess()
            && $this->unmanagedRepository->isExistForAccountId($this->getAccount()->getId());
    }

    public function isNeedShowInProgressButton(): bool
    {
        return $this->isDownloadUnmanagedInProcess();
    }

    private function isDownloadUnmanagedInProcess(): bool
    {
        return $this->accountLockManager->isExistByAccount($this->getAccount());
    }

    // ----------------------------------------

    private function getAccount(): \M2E\Otto\Model\Account
    {
        return $this->uiAccountRuntimeStorage->getAccount();
    }
}
