<?php

namespace M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker;

use M2E\Otto\Model\Template\Synchronization;

class NotListedChecker extends \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker
{
    private \M2E\Otto\Model\ScheduledAction\CreateService $scheduledActionCreate;
    private \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\Otto\Model\Product\ActionCalculator $actionCalculator;

    public function __construct(
        \M2E\Otto\Model\Product\ActionCalculator $actionCalculator,
        \M2E\Otto\Model\ScheduledAction\CreateService $scheduledActionCreate,
        \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\Input $input
    ) {
        parent::__construct($input);

        $this->actionCalculator = $actionCalculator;
        $this->scheduledActionCreate = $scheduledActionCreate;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function isAllowed(): bool
    {
        if (!parent::isAllowed()) {
            return false;
        }

        $listingProduct = $this->getInput()->getListingProduct();

        if (
            !$listingProduct->isListable()
            || !$listingProduct->isStatusNotListed()
            || !$listingProduct->getListing()->isDescriptionPolicyExist()
            || !$listingProduct->getListing()->isShippingPolicyExist()
        ) {
            return false;
        }

        return true;
    }

    public function process(): void
    {
        $product = $this->getInput()->getListingProduct();

        $calculateResult = $this->actionCalculator->calculateToList($product);
        if (!$calculateResult->isActionList()) {
            $this->tryRemoveExistScheduledAction();

            return;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeList()
        ) {
            return;
        }

        $this->scheduledActionCreate->create(
            $this->getInput()->getListingProduct(),
            \M2E\Otto\Model\Product::ACTION_LIST,
            \M2E\Otto\Model\Product::STATUS_CHANGER_SYNCH,
            [],
            $calculateResult->getConfigurator()->getAllowedDataTypes(),
            false,
            $calculateResult->getConfigurator()
        );
    }

    private function tryRemoveExistScheduledAction(): void
    {
        if ($this->getInput()->getScheduledAction() === null) {
            return;
        }

        if ($this->getInput()->getScheduledAction()->isForce()) {
            return;
        }

        $this->scheduledActionRepository->remove($this->getInput()->getScheduledAction());
    }
}
