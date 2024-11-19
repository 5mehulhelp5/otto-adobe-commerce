<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel;

abstract class AbstractMain extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    private \M2E\Otto\Model\Module $module;

    public function __construct(
        \M2E\Otto\Model\Module $module
    ) {
        parent::__construct();

        $this->module = $module;
    }

    public function _isAllowed(): bool
    {
        return true;
    }

    protected function _validateSecretKey(): bool
    {
        return true;
    }

    protected function init(): void
    {
        $this->addCss('control_panel.css');

        $title = __('Control Panel')
            . ' (M2E Otto ' . $this->module->getPublicVersion() . ')';

        $this->getResultPage()->getConfig()->getTitle()->prepend($title);
    }

    /**
     * It will allow to use control panel features even if extension is disabled, etc.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return bool
     */
    protected function preDispatch(\Magento\Framework\App\RequestInterface $request)
    {
        return true;
    }
}
