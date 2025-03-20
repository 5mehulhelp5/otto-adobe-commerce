<?php

namespace M2E\Otto\Observer\Indexes;

class Disable extends \M2E\Otto\Observer\AbstractObserver
{
    /** @var \M2E\Otto\Model\Magento\Product\Index */
    private $productIndex;
    private \M2E\Core\Helper\Magento $helperMagento;

    public function __construct(
        \M2E\Core\Helper\Magento $helperMagento,
        \M2E\Otto\Model\Magento\Product\Index $productIndex
    ) {
        $this->productIndex = $productIndex;
        $this->helperMagento = $helperMagento;
    }

    protected function process(): void
    {
        if ($this->helperMagento->isMSISupportingVersion()) {
            return;
        }

        if (!$this->productIndex->isIndexManagementEnabled()) {
            return;
        }

        foreach ($this->productIndex->getIndexes() as $code) {
            if ($this->productIndex->disableReindex($code)) {
                $this->productIndex->rememberDisabledIndex($code);
            }
        }
    }
}
