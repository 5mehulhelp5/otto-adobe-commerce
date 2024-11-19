<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

class TemplateGrid extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate
{
    public function execute()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Otto\Template\Grid $switcherBlock */
        $grid = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Template\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
