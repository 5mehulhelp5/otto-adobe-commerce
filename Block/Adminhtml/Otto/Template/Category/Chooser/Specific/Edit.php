<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific;

class Edit extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private string $title;
    private array $attributes;
    private array $customAttributes;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        string $title,
        array $attributes = [],
        array $customAttributes = [],
        array  $data = []
    ) {
        parent::__construct($context, $data);

        $this->title = $title;
        $this->attributes = $attributes;
        $this->customAttributes = $customAttributes;
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('ottoTemplateCategoryChooserSpecificEdit');

        $this->_controller = 'adminhtml_Otto_template_category_chooser_specific';
        $this->_mode = 'edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    public function prepareFormData(): void
    {
        $formData = [
            'real_attributes' => $this->attributes,
            'custom_attributes' => $this->customAttributes,
        ];

        $this->getChildBlock('form')
             ->setData('form_data', $formData);
    }

    protected function _toHtml()
    {
        $infoBlock = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific\Info::class,
            '',
            ['data' => ['title' => $this->title]]
        );

        $this->jsTranslator->addTranslations(
            [
                'Item Specifics cannot have the same Labels.' => __(
                    'Item Specifics cannot have the same Labels.'
                ),
            ]
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

        $this->js->add(
            <<<JS
    require([
        'Otto/Otto/Template/Category/Specifics'
    ], function(){
        window.OttoTemplateCategorySpecificsObj = new OttoTemplateCategorySpecifics();
    });
JS
        );

        $parentHtml = parent::_toHtml();

        return <<<HTML
<div id="chooser_container_specific">

    <div style="margin-top: 15px;">
        {$infoBlock->_toHtml()}
    </div>

    <div id="Otto-category-chooser-specific" overflow: auto;">
        {$parentHtml}
    </div>

</div>
HTML;
    }
}
