define([
    'Otto/Grid',
    'prototype'
], function () {

    window.OttoListingGrid = Class.create(Grid, {

        // ---------------------------------------

        backParam: base64_encode('*/otto_listing/index'),

        // ---------------------------------------

        prepareActions: function () {
            return false;
        },

        // ---------------------------------------

        addProductsSourceProductsAction: function (id) {
            setLocation(Otto.url.get('otto_listing_product_add/index', {
                id: id,
                source: 'product',
                clear: true,
                back: this.backParam
            }));
        },

        // ---------------------------------------

        addProductsSourceCategoriesAction: function (id) {
            setLocation(Otto.url.get('otto_listing_product_add/index', {
                id: id,
                source: 'category',
                clear: true,
                back: this.backParam
            }));
        }

        // ---------------------------------------
    });

});
