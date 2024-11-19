<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Order\Edit\ShippingAddress;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm;

class Form extends AbstractForm
{
    private \M2E\Otto\Helper\Data $dataHelper;
    private \M2E\Otto\Helper\Magento $magentoHelper;
    private \M2E\Otto\Model\Order $order;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Magento $magentoHelper,
        \M2E\Otto\Model\Order $order,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->dataHelper = $dataHelper;
        $this->magentoHelper = $magentoHelper;
        $this->order = $order;
    }

    protected function _prepareForm()
    {
        $order = $this->order;

        $buyerEmail = $order->getBuyerEmail();
        if (stripos($buyerEmail, 'Invalid Request') !== false) {
            $buyerEmail = '';
        }

        try {
            $regionCode = $order->getShippingAddress()->getRegionCode();
        } catch (\Exception $e) {
            $regionCode = null;
        }

        $state = $order->getShippingAddress()->getState();

        if (empty($regionCode) && !empty($state)) {
            $regionCode = $state;
        }

        $address = $order->getShippingAddress()->getData();

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'order_address_info',
            [
                'legend' => __('Order Address Information'),
            ]
        );

        $fieldset->addField(
            'buyer_name',
            'text',
            [
                'name' => 'buyer_name',
                'label' => __('Buyer Name'),
                'value' => $order->getBuyerName(),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'buyer_email',
            'text',
            [
                'name' => 'buyer_email',
                'label' => __('Buyer Email'),
                'value' => $buyerEmail,
                'required' => true,
            ]
        );

        $fieldset->addField(
            'recipient_name',
            'text',
            [
                'name' => 'recipient_name',
                'label' => __('Recipient Name'),
                'value' => isset($address['recipient_name'])
                    ? \M2E\Otto\Helper\Data::escapeHtml($address['recipient_name']) : '',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'street_0',
            'text',
            [
                'name' => 'street[0]',
                'label' => __('Street Address'),
                'value' => isset($address['street'])
                    ? \M2E\Otto\Helper\Data::escapeHtml($address['street']) : '',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'value' => $address['city'],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'country_code',
            'select',
            [
                'name' => 'country_code',
                'label' => __('Country'),
                'values' => $this->magentoHelper->getCountries(),
                'value' => $address['country_code'],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'postal_code',
            'text',
            [
                'name' => 'postal_code',
                'label' => __('Zip/Postal Code'),
                'value' => $address['postal_code'],
            ]
        );

        $fieldset->addField(
            'phone',
            'text',
            [
                'name' => 'phone',
                'label' => __('Telephone'),
                'value' => $address['phone'],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $this->jsUrl->addUrls($this->dataHelper->getControllerActions('Order'));
        $this->jsUrl->add(
            $this->getUrl(
                '*/otto_order_shippingAddress/save',
                ['order_id' => $this->getRequest()->getParam('id')]
            ),
            'formSubmit'
        );

        return parent::_prepareForm();
    }
}
