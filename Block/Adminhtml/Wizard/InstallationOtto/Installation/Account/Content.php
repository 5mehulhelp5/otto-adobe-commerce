<?php

namespace M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto\Installation\Account;

class Content extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Otto\Block\Adminhtml\Otto\Account\Add\FormFactory $addAccountformFactory;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Otto\Block\Adminhtml\Otto\Account\Add\FormFactory $addAccountformFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->addAccountformFactory = $addAccountformFactory;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('wizardInstallationWizardTutorial');
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            __(
                'On this step, you should link your %channel_title Account with your %extension_title.<br/><br/>',
                [
                    'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                    'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                ]
            )
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $url = $this->getUrl('*/wizard_installationOtto/beforeGetInstallationId');

        $mode = \M2E\Otto\Model\Account::MODE_PRODUCTION;
        if ($this->getRequest()->getParam('mode') === \M2E\Otto\Model\Account::MODE_SANDBOX) {
            $mode = \M2E\Otto\Model\Account::MODE_SANDBOX;
        }

        if ($this->getRequest()->getParam('install') !== null) {
            $url = $this->getUrl('*/wizard_installationOtto/beforeGetToken');
        }

        $form = $this->addAccountformFactory->create($url, $mode);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsTranslator->add(
            'An error during of account creation.',
            __(
                'The %channel_title token obtaining is currently unavailable. Please try again later.',
                [
                    'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                ]
            )
        );

        return parent::_beforeToHtml();
    }
}
