<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Unmanaged\Column;

use M2E\Otto\Model\Product;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    private const STATUS_INCOMPLETE = 'Incomplete';

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $status = (int)$row['status'];
            if ((int)$row['is_incomplete'] === 1) {
                $row['status'] = '<span style="color: orange;">' . self::STATUS_INCOMPLETE . '</span>';
                continue;
            }
            $statusTitle = $this->mapStatus($status);

            $row['status'] = $statusTitle;
        }

        return $dataSource;
    }

    private function mapStatus(int $status): string
    {
        if ($status === Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Product::getStatusTitle(Product::STATUS_NOT_LISTED) . '</span>';
        }

        if ($status === Product::STATUS_LISTED) {
            return '<span style="color: green;">' . Product::getStatusTitle(Product::STATUS_LISTED) . '</span>';
        }

        if ($status === Product::STATUS_INACTIVE) {
            return '<span style="color: red;">' . Product::getStatusTitle(Product::STATUS_INACTIVE) . '</span>';
        }

        return '';
    }
}
