<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Template;

class CheckMessages extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    public function execute()
    {
        $this->setJsonContent(['messages' => '']);

        return $this->getResult();
    }
}
