<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Unmanaged\Column;

class Qty extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $qty = $row['qty'];

            if ((int)$row['status'] === \M2E\Otto\Model\Product::STATUS_INACTIVE) {
                $row['qty'] = sprintf(
                    '<span style="color: gray">%s</span>',
                    __('N/A')
                );
                continue;
            }

            if ($qty <= 0) {
                $row['qty'] = 0;

                continue;
            }

            $row['qty'] = $qty;
        }

        return $dataSource;
    }
}
