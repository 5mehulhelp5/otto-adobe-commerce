<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific\Form\Element;

use M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific\Form\Element\Dictionary\Multiselect;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class Dictionary extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    use \M2E\Otto\Block\Adminhtml\Traits\BlockTrait;

    public \Magento\Framework\View\LayoutInterface $layout;
    private \M2E\Otto\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        \M2E\Otto\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        $this->layout = $context->getLayout();
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('specifics');
    }

    //########################################

    public function getElementHtml()
    {
        return '';
    }

    //########################################

    public function getSpecifics()
    {
        return $this->getData('specifics');
    }

    private function makeInputName(int $index, string $key): string
    {
        return sprintf(
            '%s[dictionary_%s][%s]',
            $this->getId(),
            $index,
            $key
        );
    }

    private function makeElementId(int $index, string $key, string $customIndex = ''): string
    {
        $id = sprintf(
            '%s_dictionary_%s_%s',
            $this->getId(),
            $key,
            $index
        );

        if ($customIndex !== '') {
            $id .= "_$customIndex";
        }

        return $id;
    }

    //########################################

    public function getAttributeIdHiddenHtml($index, $specific): string
    {
        $element = $this->_factoryElement->create('hidden', [
            'data' => [
                'name' => $this->makeInputName($index, 'attribute_id'),
                'class' => 'otto-dictionary-specific-attribute-id collected-attribute',
                'value' => $specific['id'],
            ],
        ]);
        $element->setForm($this->getForm());

        return $element->getElementHtml();
    }

    public function getAttributeNameHiddenHtml($index, $specific): string
    {
        $element = $this->_factoryElement->create('hidden', [
            'data' => [
                'name' => $this->makeInputName($index, 'attribute_name'),
                'class' => 'otto-dictionary-specific-attribute-id collected-attribute',
                'value' => $specific['title'],
            ],
        ]);
        $element->setForm($this->getForm());

        return $element->getElementHtml();
    }

    public function getAttributeTypeHiddenHtml(int $index, $attribute)
    {
        $element = $this->_factoryElement->create('hidden', [
            'data' => [
                'name' => $this->makeInputName($index, 'attribute_type'),
                'class' => 'otto-dictionary-specific-attribute-id collected-attribute',
                'value' => $attribute['attribute_type'],
            ],
        ]);
        $element->setForm($this->getForm());

        return $element->getElementHtml();
    }

    public function getModeHtml($index): string
    {
        $element = $this->_factoryElement->create('hidden', [
            'data' => [
                'name' => $this->makeInputName($index, 'mode'),
                'class' => 'specific_mode collected-attribute',
                'value' => \M2E\Otto\Model\Otto\Template\Category::MODE_ITEM_SPECIFICS,
            ],
        ]);

        $element->setForm($this->getForm());
        $element->setId($this->makeElementId($index, 'mode'));

        return $element->getElementHtml();
    }

    public function getAttributeTitleLabelHtml($index, $specific): string
    {
        $required = '';
        if ($specific['required']) {
            $required = '&nbsp;<span class="required">*</span>';
        }

        return sprintf(
            '<span id="%s">%s%s</span>',
            $this->makeElementId($index, 'attribute_title_label'),
            $specific['title'],
            $required
        );
    }

    public function getValueModeSelectHtml($index, $specific): string
    {
        $values = [
            \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_NONE => [
                'value' => \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_NONE,
                'label' => __('None'),
            ],
            \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_OTTO_RECOMMENDED => [
                'value' => \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_OTTO_RECOMMENDED,
                'label' => __('Otto Recommended'),
            ],
            \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_ATTRIBUTE => [
                'value' => \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_ATTRIBUTE,
                'label' => __('Custom Attribute'),
            ],
            \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_VALUE => [
                'value' => \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_VALUE,
                'label' => __('Custom Value'),
            ],
        ];

        if ($specific['required']) {
            $values[\M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_NONE] = [
                'label' => '',
                'value' => '',
                'style' => 'display: none',
            ];
        }

        if ($specific['type'] === \M2E\Otto\Model\Otto\Template\Category::RENDER_TYPE_TEXT) {
            unset($values[\M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_OTTO_RECOMMENDED]);
        }

        if (empty($specific['values_allowed'])) {
            unset($values[\M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_OTTO_RECOMMENDED]);
        }

        /** @var \Magento\Framework\Data\Form\Element\Select $element */
        $element = $this->_factoryElement->create('select', [
            'data' => [
                'name' => $this->makeInputName($index, 'value_mode'),
                'style' => 'width: 100%',
                'onchange' => "OttoTemplateCategorySpecificsObj.dictionarySpecificModeChange('{$index}', this);",
                'value' => !empty($specific['template_attribute']) ?
                    $specific['template_attribute']['value_mode'] : null,
                'values' => $values,
            ],
        ]);

        $element->setNoSpan(true);
        $element->setClass('Otto-required-when-visible input-specific-value-mode collected-attribute');
        $element->setForm($this->getForm());
        $element->setId($this->makeElementId($index, 'value_mode'));

        return $element->getElementHtml();
    }

    public function getValueOttoRecommendedHtml($index, $specific): string
    {
        $values = [];
        foreach ($specific['values_allowed'] as $value) {
            $values[] = [
                'label' => $value,
                'value' => $value
            ];
        }

        $display = 'display: none;';
        $disabled = true;
        if (
            isset($specific['template_attribute']['value_mode']) &&
            $specific['template_attribute']['value_mode']
            == \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_OTTO_RECOMMENDED
        ) {
            $display = '';
            $disabled = false;
        }

        if (
            $specific['type'] == \M2E\Otto\Model\Otto\Template\Category::RENDER_TYPE_SELECT_MULTIPLE ||
            $specific['type'] == \M2E\Otto\Model\Otto\Template\Category::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT
        ) {
            /** @var \Magento\Framework\Data\Form\Element\Select $element */
            $element = $this->_factoryElement->create(
                Multiselect::class,
                [
                    'data' => [
                        'class' => 'collected-attribute',
                        'name' => $this->makeInputName($index, 'value_otto_recommended'),
                        'style' => 'width: 100%;' . $display,
                        'value' => empty($specific['template_attribute']['value_otto_recommended'])
                            ? []
                            : $specific['template_attribute']['value_otto_recommended'],
                        'values' => $values,
                        'data-min_values' => $specific['min_values'],
                        'data-max_values' => $specific['max_values'],
                        'disabled' => $disabled,
                    ],
                ]
            );
        } else {
            array_unshift(
                $values,
                [
                    'label' => '',
                    'value' => '',
                    'style' => 'display: none',
                ]
            );

            /** @var \Magento\Framework\Data\Form\Element\Select $element */
            $element = $this->_factoryElement->create('select', [
                'data' => [
                    'name' => $this->makeInputName($index, 'value_otto_recommended'),
                    'style' => 'width: 100%;' . $display,
                    'value' => empty($specific['template_attribute']['value_otto_recommended'])
                        ? ''
                        : $specific['template_attribute']['value_otto_recommended'],
                    'values' => $values,
                    'disabled' => $disabled,
                    'class' => 'collected-attribute',
                ],
            ]);
        }

        $element->addClass('Otto-required-when-visible');
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId($this->makeElementId($index, 'value_otto_recommended'));

        return $element->getElementHtml();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getValueCustomValueHtml($index, $specific): string
    {
        if (empty($specific['template_attribute']['value_custom_value'])) {
            $customValues = '';
        } else {
            $customValues = $specific['template_attribute']['value_custom_value'];
        }

        $display = 'display: none;';
        $disabled = true;
        if (
            isset($specific['template_attribute']['value_mode']) &&
            $specific['template_attribute']['value_mode']
            == \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_VALUE
        ) {
            $display = '';
            $disabled = false;
        }

        $exampleValues = $specific['values_example'];
        /** @var \Magento\Framework\Data\Form\Element\Text $element */
        $element = $this->_factoryElement->create('text', [
            'data' => [
                'name' => $this->makeInputName($index, 'value_custom_value'),
                'style' => 'width: 100%;',
                'class' => 'Otto-required-when-visible item-specific collected-attribute',
                'value' => $customValues,
                'disabled' => $disabled,
                'placeholder' => !empty($exampleValues) ? 'Example: ' . implode(', ', $exampleValues) : '',
            ],
        ]);
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId($this->makeElementId($index, 'value_custom_value'));

        $customValueRows = <<<HTML
    <tr>
        <td style="border: none; width: 100%; vertical-align:top; text-align: left; padding: 0 0 2px; 0">
            {$element->getHtml()}
        </td>
    </tr>
HTML;

        $divId = $this->makeElementId($index, 'custom_value_table');
        $tableBodyId = $this->makeElementId($index, 'custom_value_table_body');

        return <<<HTML
    <div id="$divId" style="$display">
        <table style="width: 100%">
            <tbody id="$tableBodyId">
                {$customValueRows}
            </tbody>
        </table>
    </div>
HTML;
    }

    public function getValueCustomAttributeHtml($index, $specific): string
    {
        $attributes = $this->magentoAttributeHelper->getAll();

        foreach ($attributes as &$attribute) {
            $attribute['value'] = $attribute['code'];
            unset($attribute['code']);
        }

        $display = 'display: none;';
        $disabled = true;
        if (
            isset($specific['template_attribute']['value_mode']) &&
            $specific['template_attribute']['value_mode']
            == \M2E\Otto\Model\Otto\Template\Category::VALUE_MODE_CUSTOM_ATTRIBUTE
        ) {
            $display = '';
            $disabled = false;
        }

        /** @var \Magento\Framework\Data\Form\Element\Select $element */
        $element = $this->_factoryElement->create('select', [
            'data' => [
                'name' => $this->makeInputName($index, 'value_custom_attribute'),
                'style' => 'width: 100%;' . $display,
                'class' => 'Otto-custom-attribute-can-be-created collected-attribute',
                'value' => empty($specific['template_attribute']['value_custom_attribute']) ?
                    '' :
                    $specific['template_attribute']['value_custom_attribute'],
                'values' => $attributes,
                'apply_to_all_attribute_sets' => 0,
                'disabled' => $disabled,
            ],
        ]);

        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId($this->makeElementId($index, 'value_custom_attribute'));

        return $element->getElementHtml();
    }

    public function getAttributeDescriptionHint(array $specific): string
    {
        $description = $specific['description'];

        if (empty($description) || $specific['description'] === $specific['title']) {
            return '';
        }

        return $this->getTooltipHtml($description, true, ['no-margin-top', 'Otto-attribute-tooltip']);
    }
}
