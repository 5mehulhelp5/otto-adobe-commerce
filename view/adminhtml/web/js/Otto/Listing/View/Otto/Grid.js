define([
    'Magento_Ui/js/modal/modal',
    'Otto/Plugin/Messages',
    'Otto/Otto/Listing/View/Grid'
], function (modal, MessageObj) {

    window.OttoListingViewOttoGrid = Class.create(OttoListingViewGrid, {

        // ---------------------------------------

        afterInitPage: function ($super) {
            $super();

            $(this.gridId + '_massaction-select').observe('change', function () {
                if (!$('get-estimated-fee')) {
                    return;
                }

                if (this.value == 'list') {
                    $('get-estimated-fee').show();
                } else {
                    $('get-estimated-fee').hide();
                }
            });
        },

        // ---------------------------------------

        getMaxProductsInPart: function () {
            return 10;
        },

        // ---------------------------------------

        getLogViewUrl: function (rowId) {
            var idField = Otto.php.constant('\\M2E\\Otto\\Block\\Adminhtml\\Log\\Listing\\Product\\AbstractGrid::LISTING_PRODUCT_ID_FIELD');

            var params = {};
            params[idField] = rowId;

            return Otto.url.get('otto_log_listing_product/index', params);
        },

        // ---------------------------------------

        openFeePopUp: function (content, title) {
            var feePopup = $('fee_popup');

            if (feePopup) {
                feePopup.remove();
            }

            $('html-body').insert({bottom: '<div id="fee_popup"></div>'});

            $('fee_popup').update(content);

            var popup = jQuery('#fee_popup');

            modal({
                title: title,
                type: 'popup',
                buttons: [{
                    text: Otto.translator.translate('Close'),
                    class: 'action-secondary',
                    click: function () {
                        popup.modal('closeModal');
                    }
                }]
            }, popup);

            popup.modal('openModal');
        },

        // ---------------------------------------
    });
});
