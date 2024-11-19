<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account;

class Edit extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private ?\M2E\Otto\Model\Account $account;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        ?\M2E\Otto\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_controller = 'adminhtml_otto_account';

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($this->getRequest()->getParam('close_on_save', false)) {
            if ($this->getRequest()->getParam('id')) {
                $this->addButton('save', [
                    'label' => __('Save And Close'),
                    'onclick' => 'OttoAccountObj.saveAndClose()',
                    'class' => 'primary',
                ]);
            } else {
                $this->addButton('save_and_continue', [
                    'label' => __('Save And Continue Edit'),
                    'onclick' => 'OttoAccountObj.saveAndEditClick(\'\',\'ottoTabs\')',
                    'class' => 'primary',
                ]);
            }

            return;
        }

        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => 'OttoAccountObj.backClick(\'' . $this->getUrl('*/otto_account/index') . '\')',
            'class' => 'back',
        ]);

        $saveButtonsProps = [];
        $this->addButton('delete', [
            'label' => __('Delete'),
            'onclick' => 'OttoAccountObj.deleteClick()',
            'class' => 'delete otto_delete_button primary',
        ]);

        $saveButtonsProps['save'] = [
            'label' => __('Save And Back'),
            'onclick' => 'OttoAccountObj.saveClick()',
            'class' => 'save primary',
        ];

        $saveButtons = [
            'id' => 'save_and_continue',
            'label' => __('Save And Continue Edit'),
            'class' => 'add',
            'button_class' => '',
            'onclick' => 'OttoAccountObj.saveAndEditClick(\'\', \'ottoAccountEditTabs\')',
            'class_name' => \M2E\Otto\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => $saveButtonsProps,
        ];

        $this->addButton('save_buttons', $saveButtons);
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\Otto\Block\Adminhtml\Otto\Account\Edit\Form::class);

        return parent::_prepareLayout();
    }
}
