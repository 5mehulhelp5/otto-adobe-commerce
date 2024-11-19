<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\General;

class MsiNotificationPopupClose extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    private \M2E\Otto\Model\Registry\Manager $registry;

    public function __construct(
        \M2E\Otto\Model\Registry\Manager $registry
    ) {
        parent::__construct();

        $this->registry = $registry;
    }

    public function execute()
    {
        $this->registry->setValue('/view/msi/popup/shown/', 1);
        $this->setJsonContent(['status' => true]);

        return $this->getResult();
    }
}
