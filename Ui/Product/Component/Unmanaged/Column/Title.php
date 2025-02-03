<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Unmanaged\Column;

class Title extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $productTitle = $row['title'];

            $html = sprintf('<p>%s</p>', $productTitle);

            $html .= $this->renderLine((string)\__('SKU'), $row['sku']);
            $html .= $this->renderLine((string)\__('Category'), $row['category']);

            $row['title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0"><strong>%s:</strong> %s</p>', $label, $value);
    }
}
