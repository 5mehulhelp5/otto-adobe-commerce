define([
    'jquery',
    'Otto/Otto/Listing/View/Grid',
    'Otto/Listing/MovingFromListing',
    'Otto/Listing/Wizard/Category',
    'Magento_Ui/js/modal/modal'
], function (jQuery) {

    window.OttoListingViewSettingsGrid = Class.create(OttoListingViewGrid, {
        accountId: null,

        // ---------------------------------------

        initialize: function ($super, gridId, listingId, accountId) {
            this.accountId = accountId;

            $super(gridId, listingId);
        },

        // ---------------------------------------

        prepareActions: function ($super) {
            $super();

            this.movingHandler = new MovingFromListing(this);
            this.categoryHandler = new OttoListingCategory(this);

            this.actions = Object.extend(this.actions, {
                remapProductAction: function (id) {
                    this.mappingHandler.openPopUp(id, null, this.listingId);
                }.bind(this),
                movingAction: this.movingHandler.run.bind(this.movingHandler),
                editCategorySettingsAction: this.categoryHandler.editCategorySettings.bind(this.categoryHandler),
            });
        },

        // ---------------------------------------

        tryToMove: function (listingId) {
            this.movingHandler.submit(listingId, this.onSuccess)
        },

        onSuccess: function () {
            this.unselectAllAndReload();
        },

        // ---------------------------------------

        confirm: function (config) {
            if (config.actions && config.actions.confirm) {
                config.actions.confirm();
            }
        },
    });
});
