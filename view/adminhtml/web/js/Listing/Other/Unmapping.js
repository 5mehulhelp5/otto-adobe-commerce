define([
    'jquery',
    'Otto/Plugin/Messages',
    'Otto/Action'
], function (jQuery, MessagesObj) {

    window.ListingOtherUnmapping = Class.create(Action, {

        // ---------------------------------------

        run: function () {
            this.unmappingProducts(
                    this.gridHandler.getSelectedProductsString()
            );
        },

        unmappingProducts: function (productsString) {
            new Ajax.Request(Otto.url.get('unmappingProducts'), {
                method: 'post',
                parameters: {
                    product_ids: productsString
                },
                onSuccess: (function (transport) {

                    MessagesObj.clear();

                    if (transport.responseText == '1') {
                        MessagesObj.addSuccess(
                                Otto.translator.translate('Product(s) was Unlinked.')
                        );
                    } else {
                        MessagesObj.addError(
                                Otto.translator.translate('Not enough data')
                        );
                    }

                    this.gridHandler.unselectAllAndReload();
                }).bind(this)
            });
        }

        // ---------------------------------------
    });
});
