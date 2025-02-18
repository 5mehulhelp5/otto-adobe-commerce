<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Order;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer;

class View extends AbstractContainer
{
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Otto\Helper\Url $urlHelper;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Otto\Helper\Url $urlHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoOrderView');
        $this->_controller = 'adminhtml_otto_order';
        $this->_mode = 'view';

        /** @var \M2E\Otto\Model\Order $order */
        $order = $this->globalDataHelper->getValue('order');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->urlHelper->getBackUrl('*/otto_order/index');
        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => 'CommonObj.backClick(\'' . $url . '\')',
            'class' => 'back',
        ]);

        if ($order->getReserve()->isPlaced()) {
            $url = $this->getUrl('*/order/reservationCancel', ['ids' => $order->getId()]);
            $this->addButton('reservation_cancel', [
                'label' => __('Cancel QTY Reserve'),
                'onclick' => "confirmSetLocation(Otto.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        } elseif ($order->isReservable()) {
            $url = $this->getUrl('*/order/reservationPlace', ['ids' => $order->getId()]);
            $this->addButton('reservation_place', [
                'label' => __('Reserve QTY'),
                'onclick' => "confirmSetLocation(Otto.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        }

        if ($order->canCreateMagentoOrder()) {
            $url = $this->getUrl('*/*/createMagentoOrder', ['id' => $order->getId()]);
            $this->addButton('order', [
                'label' => __('Create Magento Order'),
                'onclick' => "setLocation('" . $url . "');",
                'class' => 'primary',
            ]);
        } elseif ($order->getMagentoOrder() === null || $order->getMagentoOrder()->isCanceled()) {
            // ---------------------------------------
            $url = $this->getUrl('*/*/createMagentoOrder', ['id' => $order->getId(), 'force' => 'yes']);
            $confirm = \M2E\Otto\Helper\Data::escapeJs(
                (string)__('Are you sure that you want to create new Magento Order?')
            );

            $this->addButton('order', [
                'label' => __('Create Magento Order'),
                'onclick' => "confirmSetLocation('" . $confirm . "','" . $url . "');",
                'class' => 'primary',
            ]);
        }
    }

    protected function _beforeToHtml()
    {
        $this->js->addRequireJs(['debug' => 'Otto/Order/Debug'], '');

        return parent::_beforeToHtml();
    }
}
