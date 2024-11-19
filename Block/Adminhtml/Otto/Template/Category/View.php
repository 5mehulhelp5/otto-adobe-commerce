<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category;

use M2E\Otto\Helper\Module;

class View extends \M2E\Otto\Block\Adminhtml\Magento\AbstractContainer
{
    private \M2E\Otto\Model\Category $category;

    public function __construct(
        \M2E\Otto\Model\Category $category,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->category = $category;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('ottoCategoryView');
        $this->_template = Module::IDENTIFIER . '::otto/category/view.phtml';

        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    protected function _prepareLayout()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Otto\Template\Category\View\Info $infoBlock */
        $infoBlock = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Category\View\Info::class,
            '',
            ['category' => $this->category]
        );

        /** @var \M2E\Otto\Block\Adminhtml\Otto\Template\Category\View\Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Category\View\Edit::class,
            '',
            ['category' => $this->category]
        );

        $this->setChild('info', $infoBlock);
        $this->setChild('edit', $editBlock);

        return parent::_prepareLayout();
    }

    public function getInfoHtml()
    {
        return $this->getChildHtml('info');
    }

    public function getEditHtml()
    {
        return $this->getChildHtml('edit');
    }
}
