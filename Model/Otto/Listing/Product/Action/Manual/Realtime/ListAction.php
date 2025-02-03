<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime;

class ListAction extends AbstractRealtime
{
    use \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\Otto\Model\Product::ACTION_LIST;
    }

    protected function calculateAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Product\ActionCalculator $calculator
    ): \M2E\Otto\Model\Product\Action {
        return $calculator->calculateToList($product);
    }

    protected function logAboutSkipAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Otto\Helper\Data::INITIATOR_USER,
            \M2E\Otto\Model\Listing\Log::ACTION_LIST_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipListMessage(),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
