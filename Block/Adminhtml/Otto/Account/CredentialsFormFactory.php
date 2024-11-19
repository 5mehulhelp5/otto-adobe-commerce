<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account;

class CredentialsFormFactory
{
    private \Magento\Framework\Data\FormFactory $formFactory;

    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
    }

    public function create(bool $withButton, string $id, string $submitUrl = '')
    {
        $form = $this->formFactory->create(
            [
                'data' => [
                    'id' => $id,
                    'action' => $submitUrl,
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);

        $fieldset = $form->addFieldset(
            'general_credentials',
            [
                'legend' => __('Add API Keys'),
                'collapsable' => false,
                'class' => 'fieldset admin__fieldset admin__field-control'
            ],
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'class' => 'otto-account-title',
                'label' => __('Title'),
                'value' => '',
                'required' => true,
            ],
        );

        if ($withButton) {
            $fieldset->addField(
                'submit_button',
                'submit',
                [
                    'value' => __('Get Access'),
                    'style' => '',
                    'class' => 'submit action-default Otto-fieldset field-submit_button',
                ]
            );
        }

        return $form;
    }
}
