<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Listing\Column;

class Title extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Otto\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;

    public function __construct(
        \M2E\Core\Helper\Url                                         $urlHelper,
        \M2E\Otto\Model\Product\Ui\RuntimeStorage                    $productUiRuntimeStorage,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory,
        array                                                        $components = [],
        array                                                        $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlHelper = $urlHelper;
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

            $productTitle = $product->getOnlineTitle();
            if (empty($productTitle)) {
                $productTitle = $row['name'] ?? '--';
            }

            $html = sprintf('<p>%s</p>', $productTitle);

            $html .= $this->renderLine(
                (string)__('Listing'),
                sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    $this->getListingLink($product->getListingId()),
                    $row['listing_title']
                )
            );

            $html .= $this->renderLine(
                (string)__('Account'),
                sprintf(
                    '%s',
                    $row['account_title']
                )
            );

            $html .= $this->renderLine((string)__('Product SKU'), $row['sku']);

            $row['column_title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0"><strong>%s:</strong> %s</p>', $label, $value);
    }

    private function getListingLink(int $listingId): string
    {
        $params = [
            'back' => $this->urlHelper->makeBackUrlParam('m2e_otto/product_grid/allItems'),
            'id' => $listingId,
            'view_mode' => \M2E\Otto\Block\Adminhtml\Listing\View\Switcher::VIEW_MODE_OTTO,
        ];

        $filters = [];

        return $this->urlHelper->getUrlWithFilter('m2e_otto/otto_listing/view', $filters, $params);
    }
}
