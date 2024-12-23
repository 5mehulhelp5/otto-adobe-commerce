<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs;

class AttributeMapping extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs\AttributeMapping\GpsrAttributesFieldsetFill */
    private AttributeMapping\GpsrAttributesFieldsetFill $gpsrAttributesFieldsetFill;

    public function __construct(
        AttributeMapping\GpsrAttributesFieldsetFill $gpsrAttributesFieldsetFill,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->gpsrAttributesFieldsetFill = $gpsrAttributesFieldsetFill;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'method' => 'post',
                'action' => $this->getUrl('*/*/save'),
            ],
        ]);

        // ----------------------------------------
        $this->addGpsrAttributesFieldset($form);
        // ----------------------------------------

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/otto_settings_attributeMapping/save'),
            \M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs::TAB_ID_MAPPING_ATTRIBUTES
        );

        return parent::_beforeToHtml();
    }

    private function addGpsrAttributesFieldset(\Magento\Framework\Data\Form $form): void
    {
        $fieldset = $form->addFieldset(
            'gpsr_attributes',
            [
                'legend' => __('GPSR Attributes'),
                'tooltip' => __('The GPSR Attributes section allows you to define the default mapping between
                Magento attributes and Otto attributes for the GPSR (General Product Safety Regulation) fields.
                By setting these mappings, you can automatically apply the appropriate attributes to all
                Otto categories that require GPSR, simplifying the listing process and ensuring consistency across your products.'),
                'collapsable' => true,
            ]
        );
        $this->gpsrAttributesFieldsetFill->fill($fieldset);
    }
}
