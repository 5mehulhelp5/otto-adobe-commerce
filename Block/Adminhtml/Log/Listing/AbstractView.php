<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Log\Listing;

use M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer;

abstract class AbstractView extends AbstractContainer
{
    protected View\Switcher $viewModeSwitcherBlock;
    protected \M2E\Otto\Block\Adminhtml\Account\Switcher $accountSwitcherBlock;
    protected \M2E\Otto\Block\Adminhtml\Log\UniqueMessageFilter $uniqueMessageFilterBlock;

    abstract protected function getFiltersHtml();

    protected function _prepareLayout()
    {
        $this->viewModeSwitcherBlock = $this->createViewModeSwitcherBlock();
        $this->accountSwitcherBlock = $this->createAccountSwitcherBlock();
        $this->uniqueMessageFilterBlock = $this->createUniqueMessageFilterBlock();

        switch ($this->viewModeSwitcherBlock->getSelectedParam()) {
            case \M2E\Otto\Block\Adminhtml\Log\Listing\View\Switcher::VIEW_MODE_GROUPED:
                $gridClass = \M2E\Otto\Block\Adminhtml\Otto\Log\Listing\Product\View\Grouped\Grid::class;
                break;
            case \M2E\Otto\Block\Adminhtml\Log\Listing\View\Switcher::VIEW_MODE_SEPARATED:
                $gridClass = \M2E\Otto\Block\Adminhtml\Otto\Log\Listing\Product\View\Separated\Grid::class;
                break;
            default:
                throw new \M2E\Otto\Model\Exception\Logic(
                    sprintf('Unknown selected view - %s', $this->viewModeSwitcherBlock->getSelectedParam()),
                );
        }

        $this->addChild('grid', $gridClass);

        $this->removeButton('add');

        $this->js->add(
            <<<JS
require(['Otto/Log/View'], function () {

    window.LogViewObj = new LogView();

    {$this->getChildBlock('grid')->getJsObjectName()}.initCallback = LogViewObj.processColorMapping;
    LogViewObj.processColorMapping();
});
JS
        );

        return parent::_prepareLayout();
    }

    protected function createViewModeSwitcherBlock(): \M2E\Otto\Block\Adminhtml\Log\Listing\View\Switcher
    {
        /** @var \M2E\Otto\Block\Adminhtml\Log\Listing\View\Switcher */
        return $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Log\Listing\View\Switcher::class);
    }

    protected function createAccountSwitcherBlock(): \M2E\Otto\Block\Adminhtml\Account\Switcher
    {
        /** @var \M2E\Otto\Block\Adminhtml\Account\Switcher */
        return $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Account\Switcher::class);
    }

    protected function createUniqueMessageFilterBlock()
    {
        return $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Log\UniqueMessageFilter::class)
            ->setData([
                'route' => "*/otto_log_listing_product/",
                'title' => __('Only messages with a unique Product ID'),
            ]);
    }

    protected function getStaticFilterHtml($label, $value)
    {
        return <<<HTML
<p class="static-switcher">
    <span>{$label}:</span>
    <span>{$value}</span>
</p>
HTML;
    }

    protected function _toHtml()
    {
        $pageActionsHtml = <<<HTML
<div class="page-main-actions">
    <div class="filter_block">
        {$this->viewModeSwitcherBlock->toHtml()}
        {$this->getFiltersHtml()}
    </div>
</div>
HTML;

        return $pageActionsHtml . parent::_toHtml();
    }
}
