<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard;

abstract class StepAbstract extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    use WizardTrait;

    private \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\Otto\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Otto\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
    }

    abstract protected function getStepNick(): string;

    abstract protected function process(\M2E\Otto\Model\Listing $listing);

    public function execute()
    {
        try {
            $this->initWizard();
        } catch (\M2E\Otto\Model\Listing\Wizard\Exception\NotFoundException $e) {
            $this->getMessageManager()->addError(__('Wizard not found.'));

            return $this->_redirect('*/Otto_listing/index');
        }

        if ($this->getWizardManager()->isCompleted()) {
            return $this->_redirect('*/Otto_listing/index');
        }

        if ($this->getWizardManager()->getCurrentStep()->getNick() !== $this->getStepNick()) {
            $this->getMessageManager()->addError(__('Please complete the current step to proceed.'));

            return $this->_redirect('*/Otto_listing/index');
        }

        $this->uiListingRuntimeStorage->setListing($this->getWizardManager()->getListing());

        return $this->process($this->getWizardManager()->getListing());
    }

    private function initWizard(): void
    {
        $this->loadManagerToRuntime($this->wizardManagerFactory, $this->uiWizardRuntimeStorage);
    }

    protected function getWizardManager(): \M2E\Otto\Model\Listing\Wizard\Manager
    {
        return $this->uiWizardRuntimeStorage->getManager();
    }
}
