<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\View\Edit;

use M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific\Form as AttributesForm;

class Form extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService;

    public function __construct(
        \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->attributeService = $attributeService;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/saveCategoryData'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        /** @var \M2E\Otto\Block\Adminhtml\Otto\Template\Category\View\Edit $parentBlock */
        $parentBlock = $this->getParentBlock();
        $category = $parentBlock->getCategory();

        $form->addField(
            'category_id',
            'hidden',
            [
                'name' => 'category_id',
                'value' => $category->getId(),
            ]
        );

        $fieldset = $form->addFieldset(
            'attributes',
            [
                'legend' => __('Product Attributes'),
                'collapsable' => false,
            ]
        );

        $customAttributes = $this->attributeService->getCustomAttributes($category->getId());
        if ($customAttributes !== []) {
            $this->addAttributesTable($fieldset, 'custom_attributes', $customAttributes);
        }

        $realAttributes = $this->attributeService->getProductAttributes(
            $category->getCategoryGroupId(),
            $category->getId()
        );
        if ($realAttributes !== []) {
            $this->addAttributesTable($fieldset, 'real_attributes', $realAttributes);
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->jsTranslator->add(
            'Item Specifics cannot have the same Labels.',
            'Item Specifics cannot have the same Labels.'
        );

        $this->jsPhp->addConstants(
            [
                '\M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_OTTO_RECOMMENDED' =>
                    \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_OTTO_RECOMMENDED,
                '\M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_VALUE' =>
                    \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_VALUE,
                '\M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_ATTRIBUTE' =>
                    \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_ATTRIBUTE,
                '\M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE' =>
                    \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE,
            ]
        );

        $this->js->addRequireJs(
            [
                'etcs' => 'Otto/Otto/Template/Category/Specifics',
            ],
            <<<JS
        window.OttoTemplateCategorySpecificsObj = new OttoTemplateCategorySpecifics();
JS
        );

        return parent::_prepareLayout();
    }

    private function addAttributesTable(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        string $id,
        array $attributes
    ): void {
        /** @var AttributesForm\Renderer\Dictionary $renderer */
        $renderer = $this->getLayout()->createBlock(AttributesForm\Renderer\Dictionary::class);

        $config = [
            'specifics' => $attributes,
        ];

        $field = $fieldset->addField($id, AttributesForm\Element\Dictionary::class, $config);
        $field->setRenderer($renderer);
    }
}
