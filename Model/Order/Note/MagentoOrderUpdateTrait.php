<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Note;

trait MagentoOrderUpdateTrait
{
    private \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater;

    private function updateMagentoOrderComment(
        \M2E\Otto\Model\Order $order,
        string $comment
    ): void {
        $magentoOrderModel = $order->getMagentoOrder();
        if ($magentoOrderModel === null) {
            return;
        }

        $this->magentoOrderUpdater->setMagentoOrder($magentoOrderModel);
        $this->magentoOrderUpdater->updateComments($comment);
        $this->magentoOrderUpdater->finishUpdate();
    }
}
