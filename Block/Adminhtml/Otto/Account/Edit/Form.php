<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account\Edit;

class Form extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => 'javascript:void(0)',
                    'method' => 'post',
                ],
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
