define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Otto/Plugin/Messages',
    'Otto/Action'
], function (jQuery, modal, messageObj) {
    window.OttoListingVariationProductManage = Class.create(Action, {

        // ---------------------------------------

        initialize: function ($super, gridHandler) {
            var self = this;

            $super(gridHandler);

        },

        // ---------------------------------------

        parseResponse: function (response) {
            if (!response.responseText.isJSON()) {
                return;
            }

            return response.responseText.evalJSON();
        },

        // ---------------------------------------

        openPopUp: function (productId, title, filter, variationIdFilter) {
            var self = this;

            messageObj.clear();

            new Ajax.Request(Otto.url.get('variationProductManage'), {
                method: 'post',
                parameters: {
                    product_id: productId,
                    filter: filter,
                    variation_id_filter: variationIdFilter
                },
                onSuccess: function (transport) {

                    var modalDialog = $('modal_variation_product_manage');
                    if (!modalDialog) {
                        modalDialog = new Element('div', {
                            id: 'modal_variation_product_manage'
                        });
                    } else {
                        modalDialog.innerHTML = '';
                    }

                    window.variationProductManagePopup = jQuery(modalDialog).modal({
                        title: title.escapeHTML(),
                        type: 'slide',
                        buttons: []
                    });
                    variationProductManagePopup.modal('openModal');

                    modalDialog.insert(transport.responseText);
                    modalDialog.innerHTML.evalScripts();

                    variationProductManagePopup.productId = productId;
                }
            });
        },

        closeManageVariationsPopup: function () {
            variationProductManagePopup.modal('closeModal');
        }

        // ---------------------------------------
    });

});
