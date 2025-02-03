<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Shipping\Edit\Form;

use M2E\Otto\Model\Template\Shipping;

class Data extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Data
    {
        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);

        $form = $this->_formFactory->create();

        $form->addField(
            'shipping_id',
            'hidden',
            [
                'name' => 'shipping[id]',
                'value' => $formData['id'] ?? '',
            ]
        );

        $form->addField(
            'shipping_profile_id',
            'hidden',
            [
                'name' => 'shipping[shipping_profile_id]',
                'value' => $formData['shipping_profile_id'] ?? '',
            ]
        );

        $form->addField(
            'shipping_title',
            'hidden',
            [
                'name' => 'shipping[title]',
                'value' => $this->getTitle(),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_shipping_edit_form',
            [
                'legend' => __('Shipping'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'handling_time',
            'hidden',
            [
                'name' => 'shipping[handling_time]',
                'value' => $formData['handling_time'],
            ]
        );

        $fieldset->addField(
            'handling_time_attribute',
            'hidden',
            [
                'name' => 'shipping[handling_time_attribute]',
                'value' => $formData['handling_time_attribute'],
            ]
        );

        $formData['working_days'] = array_map('strtolower', json_decode($formData['working_days'], true));
        $fieldset->addField(
            'working_days',
            'multiselect',
            [
                'name' => 'shipping[working_days]',
                'label' => __('Working Days'),
                'title' => __('Working Days'),
                'class' => 'Otto-working-days-field',
                'values' => [
                    ['value' => 'monday', 'label' => __('Monday')],
                    ['value' => 'tuesday', 'label' => __('Tuesday')],
                    ['value' => 'wednesday', 'label' => __('Wednesday')],
                    ['value' => 'thursday', 'label' => __('Thursday')],
                    ['value' => 'friday', 'label' => __('Friday')],
                    ['value' => 'saturday', 'label' => __('Saturday')],
                    ['value' => 'sunday', 'label' => __('Sunday')],
                ],
                'value' => $formData['working_days'],
                'required' => true,
                'tooltip' => __('Select the days of the week when you are available to process orders')
            ]
        );

        $fieldset->addField(
            'order_cutoff',
            'text',
            [
                'name' => 'shipping[order_cutoff]',
                'label' => __('Order Cutoff'),
                'title' => __('Order Cutoff'),
                'value' => $formData['order_cutoff'] ?? '',
                'placeholder' => 'HH:MM',
                'class' => 'Otto-validate-time',
                'required' => true,
                'tooltip' => __('Specify the order cut-off time for same-day processing. ' .
                    'This must be in 24-hour CET format and half-hour intervals.'),
            ]
        );

        $handlingModeOptions = $this->getHandlingTimeOptions();

        $fieldset->addField(
            'handling_time_mode',
            self::SELECT,
            [
                'name' => 'shipping[handling_time_mode]',
                'label' => __('Handling Time'),
                'title' => __('Handling Time'),
                'values' => $handlingModeOptions,
                'create_magento_attribute' => false,
                'class' => 'admin__control-select Otto-validate-handling-time-mode',
                'tooltip' => __(
                    'The number of working days till the order is handed over to the carrier.'
                ),
                'required' => true,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,select');

        $fieldset->addField(
            'transport_time',
            self::SELECT,
            [
                'name' => 'shipping[transport_time]',
                'label' => __('Transport Time'),
                'title' => __('Transport Time'),
                'values' => $this->getTransportTimeOptions(),
                'value' => $formData['transport_time'],
                'class' => 'admin__control-select',
                'required' => true,
                'tooltip' => __('Specify the carrier\'s time (in days) from collection to the first delivery attempt (1-99 days)')
            ]
        );

        if (!empty($formData['shipping_profile_id'])) {
            $fieldset->addField(
                'type_hidden',
                'hidden',
                [
                    'name' => 'shipping[type]',
                    'value' => $formData['type'],
                ]
            );
        }

        $fieldset->addField(
            'type',
            self::SELECT,
            [
                'name' => 'shipping[type]',
                'label' => $this->__('Type'),
                'values' => $this->getTypeDataOptions(),
                'value' => $formData['type'] ?? '',
                'required' => true,
                'tooltip' => __('Select one of the delivery types for your products.'),
                'disabled' => !empty($formData['shipping_profile_id']),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getTitle()
    {
        $template = $this->globalDataHelper->getValue('otto_template_shipping');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    private function getFormData()
    {
        $template = $this->globalDataHelper->getValue('otto_template_shipping');

        if ($template === null || $template->getId() === null) {
            return [];
        }

        return $template->getData();
    }

    private function getDefault(): array
    {
        return [
            'handling_time_mode' => \M2E\Otto\Model\Template\Shipping::HANDLING_TIME_MODE_VALUE,
            'handling_time' => 0,
            'handling_time_attribute' => '',
            'transport_time' => 0,
            'order_cutoff' => '',
            'working_days' => '[]'
        ];
    }

    public function getTypeDataOptions(): array
    {
        $types = [
            \M2E\Otto\Model\Template\Shipping::DELIVERY_TYPE_PARCEL,
            \M2E\Otto\Model\Template\Shipping::DELIVERY_TYPE_FORWARDER_PREFERREDLOCATION,
            \M2E\Otto\Model\Template\Shipping::DELIVERY_TYPE_FORWARDER_CURBSIDE,
            \M2E\Otto\Model\Template\Shipping::DELIVERY_TYPE_FORWARDER_HEAVYDUTY
        ];

        $optionsResult = [];
        foreach ($types as $type) {
            $optionsResult[] = [
                'value' => $type,
                'label' => $type
            ];
        }

        return $optionsResult;
    }

    public function getTransportTimeOptions(): array
    {
        $options = [
            [
                'value' => '',
                'label' => __('Not Set')
            ]
        ];

        $days = [1, 2, 3, 4, 5, 6, 7, 10, 15, 20, 30, 40];

        foreach ($days as $day) {
            $options[] = [
                'value' => $day,
                'label' => $day . ' ' . __('Business Day' . ($day > 1 ? 's' : ''))
            ];
        }

        return $options;
    }

    public function getHandlingTimeOptions(): array
    {
        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);

        $options = [
            [
                'value' => Shipping::HANDLING_TIME_MODE_VALUE,
                'label' => __('Not Set'),
                'attrs' => ['attribute_code' => '0'],
            ]
        ];

        $days = [1, 2, 3, 4, 5, 6, 7, 10, 15, 20, 30, 40];

        if (!empty($formData['handling_time']) && !in_array((int)$formData['handling_time'], $days, true)) {
            $days[] = (int)$formData['handling_time'];
            sort($days);
        }

        foreach ($days as $day) {
            $options[] = [
                'value' => Shipping::HANDLING_TIME_MODE_VALUE,
                'label' => $day . ' ' . __('Business Day' . ($day > 1 ? 's' : '')),
                'attrs' => ['attribute_code' => (string)$day]
            ];
        }

        if ($formData['handling_time_mode'] == Shipping::HANDLING_TIME_MODE_ATTRIBUTE) {
            $options[0]['attrs']['selected'] = 'selected';
        }

        if ($formData['handling_time_mode'] == Shipping::HANDLING_TIME_MODE_VALUE) {
            foreach ($options as &$option) {
                if ($option['attrs']['attribute_code'] == $formData['handling_time']) {
                    $option['attrs']['selected'] = 'selected';
                    break;
                }
            }
        }

        return $options;
    }

    protected function _toHtml()
    {
        $this->jsPhp->addConstants(
            [
                '\M2E\Otto\Model\Template\Shipping::HANDLING_TIME_MODE_VALUE' => Shipping::HANDLING_TIME_MODE_VALUE,
                '\M2E\Otto\Model\Template\Shipping::HANDLING_TIME_MODE_ATTRIBUTE' => Shipping::HANDLING_TIME_MODE_ATTRIBUTE,
            ]
        );

        $this->js->add(
            <<<JS
    require([
        'Otto/Otto/Template/Shipping'
        ], function() {
    window.OttoTemplateShippingObj = new OttoTemplateShipping();
      OttoTemplateShippingObj.initObservers();
    });
JS
        );
        return parent::_toHtml();
    }
}
