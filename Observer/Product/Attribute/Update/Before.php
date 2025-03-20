<?php

namespace M2E\Otto\Observer\Product\Attribute\Update;

class Before extends \M2E\Otto\Observer\AbstractObserver
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    protected function process(): void
    {
        $changedProductsIds = $this->getEventObserver()->getData('product_ids');
        if (empty($changedProductsIds)) {
            return;
        }

        /** @var \M2E\Otto\PublicServices\Product\SqlChange $changesModel */
        $changesModel = $this->objectManager->get(\M2E\Otto\PublicServices\Product\SqlChange::class);

        foreach ($changedProductsIds as $productId) {
            $changesModel->markProductChanged($productId);
        }

        $changesModel->applyChanges();
    }
}
