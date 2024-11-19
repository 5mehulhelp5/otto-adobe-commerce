<?php

namespace M2E\Otto\Controller\Adminhtml\General;

use M2E\Otto\Controller\Adminhtml\AbstractGeneral;

class GenerateAttributeCodeByLabel extends AbstractGeneral
{
    public function execute()
    {
        $label = $this->getRequest()->getParam('store_label');
        $this->setAjaxContent(\M2E\Otto\Model\Magento\Attribute\Builder::generateCodeByLabel($label), false);

        return $this->getResult();
    }
}
