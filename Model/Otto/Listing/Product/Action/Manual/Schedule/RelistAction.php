<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule;

class RelistAction extends AbstractSchedule
{
    use \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\Otto\Model\Product::ACTION_RELIST;
    }

    protected function calculateAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Product\ActionCalculator $calculator
    ): \M2E\Otto\Model\Product\Action {
        return $calculator->calculateToRelist($product, \M2E\Otto\Model\Product::STATUS_CHANGER_USER);
    }

    protected function logAboutSkipAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Otto\Helper\Data::INITIATOR_USER,
            \M2E\Otto\Model\Listing\Log::ACTION_RELIST_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipRelistMessage(),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
