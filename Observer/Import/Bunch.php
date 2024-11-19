<?php

namespace M2E\Otto\Observer\Import;

class Bunch extends \M2E\Otto\Observer\AbstractObserver
{
    /** @var \M2E\Otto\PublicServices\Product\SqlChange */
    private $publicService;
    /** @var \Magento\Catalog\Model\Product */
    private $magentoProduct;

    public function __construct(
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\PublicServices\Product\SqlChange $publicService,
        \Magento\Catalog\Model\Product $magentoProduct
    ) {
        parent::__construct($helperFactory);
        $this->publicService = $publicService;
        $this->magentoProduct = $magentoProduct;
    }

    protected function process(): void
    {
        $rowData = $this->getEvent()->getBunch();

        $productIds = [];

        foreach ($rowData as $item) {
            if (!isset($item['sku'])) {
                continue;
            }

            $id = $this->magentoProduct->getIdBySku($item['sku']);
            if ((int)$id > 0) {
                $productIds[] = $id;
            }
        }

        foreach ($productIds as $id) {
            $this->publicService->markProductChanged($id);
        }

        $this->publicService->applyChanges();
    }
}
