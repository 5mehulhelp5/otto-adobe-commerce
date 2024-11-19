<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Account\Component\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Title extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $accountTitle = $row['title'];

            $html = sprintf('<p>%s</p>', $accountTitle);

            $html .= $this->renderLine((string)\__('Installation ID'), $row['installation_id']);

            $row['title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0">%s: %s</p>', $label, $value);
    }
}
