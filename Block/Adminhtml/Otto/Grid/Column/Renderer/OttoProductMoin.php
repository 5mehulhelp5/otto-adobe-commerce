<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Grid\Column\Renderer;

class OttoProductMoin extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    use \M2E\Otto\Block\Adminhtml\Traits\BlockTrait;

    public function render(\Magento\Framework\DataObject $row): string
    {
        return $this->renderGeneral($row, false);
    }

    public function renderExport(\Magento\Framework\DataObject $row): string
    {
        return $this->renderGeneral($row, true);
    }

    public function renderGeneral(\Magento\Framework\DataObject $row, bool $isExport): string
    {
        $ottoProductMoin = $this->_getValue($row);

        if ($row->getData('status') == \M2E\Otto\Model\Product::STATUS_NOT_LISTED) {
            if ($isExport) {
                return '';
            }
        }

        if ($ottoProductMoin === null || $ottoProductMoin === '') {
            if ($isExport) {
                return '';
            }

            return __('N/A');
        }

        if ($isExport) {
            return $ottoProductMoin;
        }

        $productUrl = $row->getOttoProductUrl();
        if (empty($productUrl)) {
            return $ottoProductMoin;
        }

        return sprintf('<a href="%s" target="_blank">%s</a>', $productUrl, $ottoProductMoin);
    }
}
