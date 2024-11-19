<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Tabs;

class Recent extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoCategoryChooserCategoryRecent');
        $this->setTemplate('otto/template/category/chooser/tabs/recent.phtml');
    }
}
