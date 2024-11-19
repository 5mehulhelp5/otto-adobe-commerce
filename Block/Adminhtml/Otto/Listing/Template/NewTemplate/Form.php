<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Template\NewTemplate;

class Form extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected function _prepareForm()
    {
        if ($this->getData('nick') == '') {
            throw new \M2E\Otto\Model\Exception\Logic('You should set template "nick"');
        }

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'new_template_form_' . $this->getData('nick'),
                    'action' => 'javascript:void(0)',
                    'method' => 'post',
                ],
            ]
        );

        $form->addField(
            'new_template_form_help_block',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>Saving Policy under a distinctive title will let you easily and quickly search for
                    it in case you need to use it in a different M2E Otto Listing in the future.</p><br>
                    <p>More detailed information you can find
                    <a href="%url" target="_blank" class="external-link">here</a>.</p>',
                    ['url' => 'https://docs-m2.m2epro.com/m2e-otto-policies'],
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'new_template_fieldset',
            []
        );

        $fieldset->addField(
            'template_title_' . $this->getData('nick'),
            'text',
            [
                'name' => $this->getData('nick') . '[template_title]',
                'class' => 'Otto-validate-template-title',
                'label' => __('Title'),
                'placeholder' => __('Please specify Policy Title'),
                'required' => true,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
