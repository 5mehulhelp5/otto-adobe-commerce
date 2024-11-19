<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto;

abstract class AbstractTemplate extends AbstractMain
{
    protected \M2E\Otto\Model\Otto\Template\Manager $templateManager;

    public function __construct(
        \M2E\Otto\Model\Otto\Template\Manager $templateManager
    ) {
        parent::__construct();
        $this->templateManager = $templateManager;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::configuration_templates');
    }
}
