<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard;

abstract class AbstractInstallation extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractWizard
{
    protected function getNick(): string
    {
        return \M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK;
    }

    protected function init(): void
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Configuration of %channel Integration', ['channel' => (string)__('Otto')]));
    }
}
