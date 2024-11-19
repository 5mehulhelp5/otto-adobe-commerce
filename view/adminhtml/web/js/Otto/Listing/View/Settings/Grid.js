define([
    'jquery',
    'Otto/Otto/Listing/View/Grid',
    'Otto/Listing/MovingFromListing',
    'Otto/Listing/Wizard/Category',
    'Otto/Listing/Mapping',
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
            this.mappingHandler = new ListingMapping(this);
            this.categoryHandler = new OttoListingCategory(this);

            this.actions = Object.extend(this.actions, {
                editAllSettingsAction: function (id) {
                    this.editSettings(id,
                            Otto.php.constant('\\M2E\\Otto\\Model\\Otto\\Template\\Manager::TEMPLATE_ALL_POLICY')
                    );
                }.bind(this),
                editPriceQuantityFormatSettingsAction: function (id) {
                    this.editSettings(id,
                            Otto.php.constant('\\M2E\\Otto\\Model\\Otto\\Template\\Manager::TEMPLATE_SELLING_FORMAT')
                    );
                }.bind(this),
                editDescriptionSettingsAction: function (id) {
                    this.editSettings(id,
                            Otto.php.constant('\\M2E\\Otto\\Model\\Otto\\Template\\Manager::TEMPLATE_DESCRIPTION')
                    );
                }.bind(this),
                editSynchSettingsAction: function (id) {
                    this.editSettings(id,
                            Otto.php.constant('\\M2E\\Otto\\Model\\Otto\\Template\\Manager::TEMPLATE_SYNCHRONIZATION')
                    );
                }.bind(this),
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

        editSettings: function (id, templateNick) {
            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

            new Ajax.Request(Otto.url.get('otto_template/editListingProductsPolicy'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedProductsIds.join(','),
                    templateNick: templateNick
                },
                onSuccess: function (transport) {

                    var result = transport.responseText;

                    if (+result === 0) {
                        return;
                    }

                    this.unselectAll();

                    var title = this.getPopUpTitle(templateNick, this.getSelectedProductsTitles());

                    if (typeof this.popUp != 'undefined') {
                        var $title = this.popUp.data('mageModal').modal.find('.modal-title');
                        $title.text(title);
                    }

                    this.openPopUp(
                            title,
                            transport.responseText,
                            {
                                buttons: [{
                                    text: Otto.translator.translate('Cancel'),
                                    class: 'action-dismiss',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }, {
                                    text: Otto.translator.translate('Save'),
                                    class: 'action-primary action-accept',
                                    click: function () {
                                        OttoListingProductSettingsObj.save(function (params) {
                                            OttoListingViewSettingsGridObj.saveSettings(params);
                                        });
                                    }
                                }],
                                closed: function () {
                                    self.selectedProductsIds = [];
                                    self.selectedCategoriesData = {};

                                    return true;
                                }

                            },
                            'modal_setting_policy_action_dialog'
                    );

                    this.insertHelpLink('modal_setting_policy_action_dialog');

                }.bind(this)
            });
        },

        // ---------------------------------------

        saveSettings: function (savedTemplates) {
            var requestParams = {};

            // push information about saved templates into the request params
            // ---------------------------------------
            $H(savedTemplates).each(function (i) {
                requestParams[i.key] = i.value;
            });
            // ---------------------------------------

            // ---------------------------------------
            requestParams['products_ids'] = this.selectedProductsIds.join(',');
            // ---------------------------------------

            new Ajax.Request(Otto.url.get('otto_template/saveListingProductsPolicy'), {
                method: 'post',
                asynchronous: true,
                parameters: requestParams,
                onSuccess: function (transport) {
                    this.popUp.modal('closeModal');
                    this.getGridObj().doFilter();
                }.bind(this)
            });
        },

        // ---------------------------------------

        getSelectedProductsTitles: function () {
            if (this.selectedProductsIds.length > 3) {
                return '';
            }

            var title = '';

            // use the names of only first three products for pop up title
            for (var i = 0; i < 3; i++) {
                if (typeof this.selectedProductsIds[i] == 'undefined') {
                    break;
                }

                if (title != '') {
                    title += ', ';
                }

                title += this.getProductNameByRowId(this.selectedProductsIds[i]);
            }

            return title;
        },

        // ---------------------------------------

        getPopUpTitle: function (templateNick, productTitles) {
            var title = '',
                    templatesNames = {};

            templatesNames[
                    Otto.php.constant('\\M2E\\Otto\\Model\\Otto\\Template\\Manager::TEMPLATE_DESCRIPTION')
                    ] = Otto.translator.translate('Edit Description Policy Setting');
            templatesNames[
                    Otto.php.constant('\\M2E\\Otto\\Model\\Otto\\Template\\Manager::TEMPLATE_SELLING_FORMAT')
                    ] = Otto.translator.translate('Edit Selling Policy Setting');
            templatesNames[
                    Otto.php.constant('\\M2E\\Otto\\Model\\Otto\\Template\\Manager::TEMPLATE_SYNCHRONIZATION')
                    ] = Otto.translator.translate('Edit Synchronization Policy Setting');

            if (templatesNames[templateNick]) {
                title = templatesNames[templateNick];
            }

            var productTitlesArray = productTitles.split(',');
            if (productTitlesArray.length > 1) {
                productTitles = productTitlesArray.map(function (el) {
                    return el.trim();
                }).join('", "');
            }

            if (productTitles) {
                title += ' ' + Otto.translator.translate('For') + ' "' + productTitles + '"';
            }

            return title;
        },

        // ---------------------------------------

        confirm: function (config) {
            if (config.actions && config.actions.confirm) {
                config.actions.confirm();
            }
        },

        insertHelpLink: function (popUpElementId) {
            var modalHeader = jQuery('#' + popUpElementId)
                    .closest('.modal-inner-wrap')
                    .find('h1.modal-title');

            if (modalHeader.has('#popup_template_help_link')) {
                modalHeader.find('#popup_template_help_link').remove();
            }

            var tips = jQuery('#popup_template_help_link');
            modalHeader.append(tips);
            tips.show();
        }

        // ---------------------------------------
    });
});
