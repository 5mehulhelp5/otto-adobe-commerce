<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Listing\Column;

class OnlinePrice extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\Otto\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;
    private \M2E\Otto\Model\Currency $currency;

    public function __construct(
        \M2E\Otto\Model\Product\Ui\RuntimeStorage                    $productUiRuntimeStorage,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory,
        \M2E\Otto\Model\Currency                                     $currency,
        array                                                        $components = [],
        array                                                        $data = []
    ) {
        $this->currency = $currency;
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

            if ($product->isStatusNotListed()) {
                $row['product_online_price'] = sprintf('<span style="color: gray;">%s</span>', __('Not Listed'));

                continue;
            }

            $onlinePrice = $this->currency->formatPrice(
                \M2E\Otto\Model\Currency::CURRENCY_EUR,
                $product->getOnlineCurrentPrice()
            );

            if ($product->isStatusInactive()) {
                $row['product_online_price'] =  sprintf(
                    '<span style="color: gray;">%s</span>',
                    $onlinePrice
                );
            } else {
                $row['product_online_price'] = $onlinePrice;
            }
        }

        return $dataSource;
    }
}
