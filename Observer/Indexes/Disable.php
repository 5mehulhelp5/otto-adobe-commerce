<?php

namespace M2E\Otto\Observer\Indexes;

class Disable extends \M2E\Otto\Observer\AbstractObserver
{
    /** @var \M2E\Otto\Model\Magento\Product\Index */
    private $productIndex;

    public function __construct(
        \M2E\Otto\Model\Magento\Product\Index $productIndex,
        \M2E\Otto\Helper\Factory $helperFactory
    ) {
        $this->productIndex = $productIndex;
        parent::__construct($helperFactory);
    }

    protected function process(): void
    {
        if ($this->getHelper('Magento')->isMSISupportingVersion()) {
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
