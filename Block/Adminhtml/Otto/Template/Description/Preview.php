<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Description;

use M2E\Otto\Block\Adminhtml\Magento\AbstractBlock;

class Preview extends AbstractBlock
{
    protected $_template = 'otto/template/description/preview.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->css->addFile('otto/template.css');
    }
}
