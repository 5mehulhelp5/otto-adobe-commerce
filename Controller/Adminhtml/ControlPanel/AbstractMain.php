<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel;

abstract class AbstractMain extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    public function _isAllowed(): bool
    {
        return true;
    }

    protected function _validateSecretKey(): bool
    {
        return true;
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
