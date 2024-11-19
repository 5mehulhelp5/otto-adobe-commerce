<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto;

abstract class Installation extends \M2E\Otto\Block\Adminhtml\Wizard\Installation
{
    protected function _construct()
    {
        parent::_construct();

        $this->updateButton('continue', 'onclick', 'InstallationWizardObj.continueStep();');
    }

    protected function _toHtml()
    {
        $this->js->add(
            <<<JS
    require([
        'Otto/Wizard/InstallationOtto'
    ], function(){
        window.InstallationWizardObj = new WizardInstallationOtto();
    });
JS
        );

        return parent::_toHtml();
    }
}
