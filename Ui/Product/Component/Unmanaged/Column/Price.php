<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Price extends Column
{
    private CurrencyInterface $localeCurrency;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $price = $row['price'];

            if (empty($price)) {
                $row['price'] = __('N/A');
                continue;
            }

            if ($price <= 0) {
                $row['price'] = '<span style="color: #f00;">0</span>';
                continue;
            }

            $row['price'] = $this->localeCurrency
                ->getCurrency($row['currency'])
                ->toCurrency($price);
        }

        return $dataSource;
    }
}
