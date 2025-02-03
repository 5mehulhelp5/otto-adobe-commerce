<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Unmanaged\Column;

class Moin extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $moin = $row['moin'];

            if ($moin === null || $moin === '') {
                $row['moin'] = __('N/A');

                continue;
            }

            if ($row['otto_product_url'] === null) {
                $row['moin'] = $moin;

                continue;
            }

            $row['moin'] = sprintf('<a href="%s" target="_blank">%s</a>', $row['otto_product_url'], $moin);
        }

        return $dataSource;
    }
}
