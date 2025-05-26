<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account\Edit\Tabs;

class General extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    private ?\M2E\Otto\Model\Account $account;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Otto\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $content = __(
            'This Page shows the Environment for your %channel_title Account and details of the authorisation for
%extension_title to connect
to your %channel_title Account.<br/><br/>
If your token has expired or is not activated, click <b>Get Token</b>.<br/><br/>',
            [
                'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
            ]
        );

        $form->addField(
            'otto_accounts_general',
            self::HELP_BLOCK,
            [
                'content' => $content,
            ],
        );

        $fieldset = $form->addFieldset(
            'general',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ],
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'class' => 'Otto-account-title',
                'label' => __('Title'),
                'value' => $this->account->getTitle(),
                'tooltip' => __(
                    'Title or Identifier of %channel_title Account for your internal use.',
                    ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
                ),
            ],
        );

        $fieldset = $form->addFieldset(
            'access_details',
            [
                'legend' => __('Access Details'),
                'collapsable' => false,
            ],
        );

        $mode = $this->getRequest()->getParam('mode');
        $url = $this->getUrl(
            '*/otto_account/beforeGetToken',
            ['account_id' => $this->account->getId(), 'mode' => $mode]
        );

        $button = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Update Access Data'),
                'onclick' => 'setLocation(\'' . $url . '\');',
                'class' => 'check otto_check_button primary',
            ],
        );

        $fieldset->addField(
            'update_access_data_container',
            'label',
            [
                'label' => '',
                'after_element_html' => $button->toHtml(),
            ],
        );

        $this->setForm($form);

        $id = $this->getRequest()->getParam('id');
        $this->js->add("Otto.formData.id = '$id';");

        $this->js->add(
            <<<JS
    require([
        'Otto/Otto/Account'
    ], function(){
        window.OttoAccountObj = new OttoAccount('{$id}');
        OttoAccountObj.initObservers();
    });
JS,
        );

        return parent::_prepareForm();
    }
}
