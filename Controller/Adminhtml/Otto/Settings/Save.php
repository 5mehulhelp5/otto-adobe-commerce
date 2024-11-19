<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Settings;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractSettings;

class Save extends AbstractSettings
{
    private \M2E\Otto\Helper\Component\Otto\Configuration $configuration;

    public function __construct(
        \M2E\Otto\Helper\Component\Otto\Configuration $componentConfiguration
    ) {
        parent::__construct();

        $this->configuration = $componentConfiguration;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->setJsonContent(['success' => false]);

            return $this->getResult();
        }

        $this->configuration->setConfigValues($this->getRequest()->getParams());
        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }
}
