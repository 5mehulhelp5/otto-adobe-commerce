<?php

namespace M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker;

use M2E\Otto\Model\Magento\Product\ChangeAttributeTracker;
use M2E\Otto\Model\Product;
use M2E\Otto\Model\Template\Synchronization;
use M2E\Otto\Model\Otto\Listing\Product\Action\Configurator;
use M2E\Otto\Model\Otto\Template\ChangeProcessor\ChangeProcessorAbstract;
use M2E\Otto\Model\Template\Synchronization\ChangeProcessor as SyncChangeProcessor;
use M2E\Otto\Model\Template\Description as DescriptionPolicy;

class ActiveChecker extends \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker
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

        if (
            !$this->getInput()->hasInstructionWithTypes($this->getStopInstructionTypes())
            && !$this->getInput()->hasInstructionWithTypes($this->getReviseInstructionTypes())
        ) {
            return false;
        }

        $listingProduct = $this->getInput()->getListingProduct();

        if (!$listingProduct->isRevisable() && !$listingProduct->isStoppable()) {
            return false;
        }

        return true;
    }

    public function process(): void
    {
        $product = $this->getInput()->getListingProduct();

        $calculateResult = $this->actionCalculator->calculateToReviseOrStop(
            $product,
            $this->getInput()->hasInstructionWithTypes($this->getReviseTitleInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseDescriptionInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseImagesInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseCategoriesInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseShippingInstructionTypes())
        );

        if (
            !$calculateResult->isActionStop()
            && !$calculateResult->isActionRevise()
        ) {
            $this->tryRemoveExistScheduledAction();

            return;
        }

        if ($calculateResult->isActionStop()) {
            $this->returnWithStopAction();

            return;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeRevise()
            && $this->getInput()->getScheduledAction()->isForce()
        ) {
            return;
        }

        $this->createReviseScheduledAction(
            $product,
            $calculateResult->getConfigurator()
        );
    }

    private function tryRemoveExistScheduledAction(): void
    {
        if ($this->getInput()->getScheduledAction() === null) {
            return;
        }

        $this->scheduledActionRepository->remove($this->getInput()->getScheduledAction());
    }

    private function returnWithStopAction(): void
    {
        $scheduledAction = $this->getInput()->getScheduledAction();
        if ($scheduledAction === null) {
            $this->createStopScheduledAction($this->getInput()->getListingProduct());

            return;
        }

        if ($scheduledAction->isActionTypeStop()) {
            return;
        }

        $this->scheduledActionRepository->remove($scheduledAction);

        $this->createStopScheduledAction($this->getInput()->getListingProduct());
    }

    private function createStopScheduledAction(Product $product): void
    {
        $this->scheduledActionCreate->create(
            $product,
            \M2E\Otto\Model\Product::ACTION_STOP,
            \M2E\Otto\Model\Product::STATUS_CHANGER_SYNCH,
            [],
        );
    }

    private function createReviseScheduledAction(
        Product $product,
        Configurator $configurator
    ): void {
        $this->scheduledActionCreate->create(
            $product,
            \M2E\Otto\Model\Product::ACTION_REVISE,
            \M2E\Otto\Model\Product::STATUS_CHANGER_SYNCH,
            [],
            $configurator->getAllowedDataTypes(),
            false,
            $configurator
        );
    }

    private function getReviseInstructionTypes(): array
    {
        return array_unique(
            array_merge(
                $this->getReviseQtyInstructionTypes(),
                $this->getRevisePriceInstructionTypes(),
                $this->getReviseTitleInstructionTypes(),
                $this->getReviseDescriptionInstructionTypes(),
                $this->getReviseImagesInstructionTypes(),
                $this->getReviseCategoriesInstructionTypes(),
                $this->getReviseShippingInstructionTypes(),
            ),
        );
    }

    private function getReviseQtyInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ];
    }

    private function getRevisePriceInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
            Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
            \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ];
    }

    protected function getReviseTitleInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            \M2E\Otto\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_ENABLED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_DISABLED,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ];
    }

    protected function getReviseDescriptionInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            \M2E\Otto\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            DescriptionPolicy::INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ];
    }

    protected function getReviseImagesInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            \M2E\Otto\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ];
    }

    protected function getReviseCategoriesInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            \M2E\Otto\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED,
            Synchronization\ChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ];
    }

    protected function getReviseShippingInstructionTypes(): array
    {
        return [
            ChangeProcessorAbstract::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_SHIPPING_PROFILE_ID_CHANGED,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Otto\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Otto\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
        ];
    }

    /**
     * @return string[]
     */
    private function getStopInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            Synchronization\ChangeProcessorAbstract::INSTRUCTION_TYPE_STOP_MODE_ENABLED,
            Synchronization\ChangeProcessorAbstract::INSTRUCTION_TYPE_STOP_MODE_DISABLED,
            Synchronization\ChangeProcessorAbstract::INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED,
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
    }
}
