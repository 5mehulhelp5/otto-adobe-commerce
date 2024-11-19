<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class Index extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractListing
{
    public function execute()
    {
        if ($this->isAjax()) {
            /** @var \M2E\Otto\Block\Adminhtml\Otto\Listing\ItemsByListing\Grid $grid */
            $grid = $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Listing\ItemsByListing\Grid::class
            );
            $this->setAjaxContent($grid);

            return $this->getResult();
        }

        /** @var \M2E\Otto\Block\Adminhtml\Otto\Listing\ItemsByListing $block */
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Listing\ItemsByListing::class
        );
        $this->addContent($block);

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Items By Listing'));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-otto-listings');

        return $this->getResult();
    }
}
