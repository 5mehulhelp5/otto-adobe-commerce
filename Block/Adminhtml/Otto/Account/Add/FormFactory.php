<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account\Add;

class FormFactory
{
    private \Magento\Framework\Data\FormFactory $formFactory;

    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
    }

    public function create(string $submitUrl, string $mode)
    {
        $form = $this->formFactory->create([
            'data' => [
                'id' => 'add_account_form',
                'action' => $submitUrl,
                'method' => 'post',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'accounts',
            [
                'class' => 'add_account_fieldset',
            ]
        );

        $fieldset->addField(
            'mode',
            'hidden',
            [
                'name' => 'mode',
                'value' => $mode
            ],
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'class' => 'otto-account-title validate-no-empty',
                'label' => __('Title'),
                'value' => '',
                'required' => true,
            ],
        );

        $fieldset->addField(
            'submit_button',
            'submit',
            [
                'value' => __('Get Access'),
                'class' => 'action-primary submit',
            ],
        );

        $form->setUseContainer(true);

        return $form;
    }
}
