<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Listing\Mapping;

class View extends \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    protected $_template = 'listing/mapping/view.phtml';

    public function _construct()
    {
        $this->_controller = 'adminhtml_listing_mapping';

        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\HelpBlock::class)->addData(
            [
                'content' => __(
                    'From the list below you should select a Magento Product to which you would like the Item
                 to be linked. Click on Link To This Product link to set accordance.'
                ),
            ]
        );
        $this->setChild('help_block', $helpBlock);

        /** @var \M2E\Otto\Block\Adminhtml\Listing\Mapping\Grid $block */
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\Mapping\Grid::class,
            '',
            [
                'data' => [
                    'grid_url' => $this->getData('grid_url'),
                    'other_product_id' => $this->getData('other_product_id'),
                    'account_id' => $this->getData('account_id'),
                ],
            ]
        );

        $this->setChild('listing_mapping_grid', $block);

        parent::_beforeToHtml();
    }
}
