<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml;

class MsiNotificationPopup extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    protected function _toHtml()
    {
        $jsMessage = \M2E\Core\Helper\Data::escapeJs(
            (string)__(
                "Magento Inventory (MSI) is enabled.
                M2E Otto will update your product quantity based on Product Salable QTY. Read more
                <a target='_blank' href='%url'>here</a>.",
                ['url' => 'https://help.m2epro.com/support/solutions/articles/9000218949'],
            )
        );
        $this->js->addOnReadyJs(
            <<<JS
    require([
        'Magento_Ui/js/modal/modal'
    ],function(modal) {
        var modalDialogMessage = new Element('div');
        modalDialogMessage.innerHTML = "$jsMessage"

        var popupObj = jQuery(modalDialogMessage).modal({
                title: jQuery.mage.__('Attention'),
                type: 'popup',
                modalClass: 'width-50',
                buttons: [
                    {
                        text: 'Ok',
                        class: 'action primary',
                    }]
            });

        popupObj.modal('openModal').on('modalclosed', function() {
            new Ajax.Request("{$this->getUrl('*/general/MsiNotificationPopupClose')}",
            {
                method: 'post',
                asynchronous : true,
            });
        });
    });
JS
        );

        return parent::_toHtml();
    }
}
