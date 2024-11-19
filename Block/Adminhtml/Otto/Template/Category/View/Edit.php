<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\View;

class Edit extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\Otto\Model\Category $category;

    public function __construct(
        \M2E\Otto\Model\Category $category,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->category = $category;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->removeButton('save');

        $this->setId('ottoConfigurationCategoryViewTabsItemSpecificsEdit');
        $this->_controller = 'adminhtml_otto_template_category_view';

        $this->_headerText = '';

        $this->updateButton(
            'reset',
            'onclick',
            'OttoTemplateCategorySpecificsObj.resetSpecifics()'
        );

        $saveButtons = [
            'id' => 'save_and_continue',
            'label' => __('Save And Continue Edit'),
            'class' => 'add',
            'button_class' => '',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'save',
                        'target' => '#edit_form',
                        'eventData' => [
                            'action' => [
                                'args' => [
                                    'back' => 'edit',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'class_name' => \M2E\Otto\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => [
                'save' => [
                    'label' => __('Save And Back'),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => [
                                    'action' => [
                                        'args' => [
                                            'back' => 'categories_grid',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->addButton('save_buttons', $saveButtons);

        if (!$this->category->hasRecordsOfAttributes()) {
            $this->removeButton('reset');
            $this->removeButton('save_and_continue');
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/otto_template_category/index');
    }

    public function getCategory(): \M2E\Otto\Model\Category
    {
        return $this->category;
    }
}
