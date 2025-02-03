<?php

namespace M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker;

use M2E\Otto\Model\Product;
use M2E\Otto\Model\Template\Synchronization;
use M2E\Otto\Model\Template\Synchronization\ChangeProcessorAbstract as SyncChangeProcessorAbstract;
use M2E\Otto\Model\Otto\Template\ChangeProcessor\ChangeProcessorAbstract;

class InactiveChecker extends \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker
{
    private static array $relistInstructionTypes = [
        \M2E\Otto\Model\Magento\Product\ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
        SyncChangeProcessorAbstract::INSTRUCTION_TYPE_RELIST_MODE_ENABLED,
        SyncChangeProcessorAbstract::INSTRUCTION_TYPE_RELIST_MODE_DISABLED,
        SyncChangeProcessorAbstract::INSTRUCTION_TYPE_RELIST_SETTINGS_CHANGED,
        \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
        \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
        \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
        \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
        Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
        Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ChangeProcessorAbstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
        \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
        \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
        \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
        \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
    ];

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

        if (!$this->getInput()->hasInstructionWithTypes(self::$relistInstructionTypes)) {
            return false;
        }

        return $this->getInput()->getListingProduct()->isRelistable();
    }

    public function process(): void
    {
        $product = $this->getInput()->getListingProduct();

        $calculateResult = $this->actionCalculator->calculateToRelist($product, Product::STATUS_CHANGER_SYNCH);
        if (!$calculateResult->isActionRelist()) {
            $this->tryRemoveExistScheduledAction();

            return;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeRelist()
        ) {
            return;
        }

        $this->scheduledActionCreate->create(
            $this->getInput()->getListingProduct(),
            \M2E\Otto\Model\Product::ACTION_RELIST,
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
