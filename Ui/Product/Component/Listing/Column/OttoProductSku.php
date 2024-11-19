<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Listing\Column;

class OttoProductSku extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\Otto\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;

    public function __construct(
        \M2E\Otto\Model\Product\Ui\RuntimeStorage                    $productUiRuntimeStorage,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory,
        array                                                        $components = [],
        array                                                        $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $product = $this->productUiRuntimeStorage->findProduct((int)$row['product_id']);
            if (empty($product)) {
                continue;
            }

            $row['product_otto_product_sku'] = __('N/A');

            $ottoProductSku = $product->getOttoProductSku();

            if ($product->isStatusNotListed()) {
                $row['product_otto_product_sku'] = sprintf('<span style="color: gray;">%s</span>', __('Not Listed'));
            }

            if ($ottoProductSku === '') {
                continue;
            }

            $row['product_otto_product_sku'] = $ottoProductSku;
        }

        return $dataSource;
    }
}
