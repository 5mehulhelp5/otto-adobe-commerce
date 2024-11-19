define([
    'Magento_Ui/js/modal/modal',
    'Otto/Common',
    'extjs/ext-tree-checkbox',
    'mage/adminhtml/form'
], function (modal) {

    window.OttoAccount = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {

            jQuery.validator.addMethod('Otto-account-customer-id', function (value) {

                var checkResult = false;

                if ($('magento_orders_customer_id_container').getStyle('display') == 'none') {
                    return true;
                }

                new Ajax.Request(Otto.url.get('general/checkCustomerId'), {
                    method: 'post',
                    asynchronous: false,
                    parameters: {
                        customer_id: value,
                        id: Otto.formData.id
                    },
                    onSuccess: function (transport) {
                        checkResult = transport.responseText.evalJSON()['ok'];
                    }
                });

                return checkResult;
            }, Otto.translator.translate('No Customer entry is found for specified ID.'));


            jQuery.validator.addMethod(
                    'Otto-require-select-attribute',
                    function (value, el) {
                        if ($('other_listings_mapping_mode').value == 0) {
                            return true;
                        }

                        var isAttributeSelected = false;

                        $$('.attribute-mode-select').each(function (obj) {
                            if (obj.value != 0) {
                                isAttributeSelected = true;
                            }
                        });

                        return isAttributeSelected;
                    },
                    Otto.translator.translate(
                            'If Yes is chosen, you must select at least one Attribute for Product Linking.'
                    )
            );
        },

        initObservers: function () {

            if ($('ottoAccountEditTabs_listingOther')) {

                $('other_listings_synchronization')
                        .observe('change', this.other_listings_synchronization_change)
                        .simulate('change');
                $('other_listings_mapping_mode')
                        .observe('change', this.other_listings_mapping_mode_change)
                        .simulate('change');
                $('mapping_sku_mode')
                        .observe('change', this.mapping_sku_mode_change)
                        .simulate('change');
                $('mapping_ean_mode')
                        .observe('change', this.mapping_ean_mode_change)
                        .simulate('change');
                $('mapping_title_mode')
                        .observe('change', this.mapping_title_mode_change)
                        .simulate('change');
            }

            if ($('ottoAccountEditTabs_order')) {

                $('magento_orders_listings_mode')
                        .observe('change', this.magentoOrdersListingsModeChange)
                        .simulate('change');
                $('magento_orders_listings_store_mode')
                        .observe('change', this.magentoOrdersListingsStoreModeChange)
                        .simulate('change');

                $('magento_orders_listings_other_mode')
                        .observe('change', this.magentoOrdersListingsOtherModeChange)
                        .simulate('change');

                $('magento_orders_number_source')
                        .observe('change', this.magentoOrdersNumberChange);
                $('magento_orders_number_prefix_prefix')
                        .observe('keyup', this.magentoOrdersNumberChange);

                OttoAccountObj.renderOrderNumberExample();

                $('magento_orders_customer_mode')
                        .observe('change', this.magentoOrdersCustomerModeChange)
                        .simulate('change');

                $('order_number_example-note').previous().remove();
            }
        },

        // ---------------------------------------

        saveAndClose: function () {
            var self = this,
                    url = typeof Otto.url.urls.formSubmit == 'undefined' ?
                            Otto.url.formSubmit + 'back/' + Base64.encode('list') + '/' :
                            Otto.url.get('formSubmit', {'back': Base64.encode('list')});

            if (!this.isValidForm()) {
                return;
            }

            new Ajax.Request(url, {
                method: 'post',
                parameters: Form.serialize($('edit_form')),
                onSuccess: function (transport) {
                    transport = transport.responseText.evalJSON();

                    if (transport.success) {
                        window.close();
                    } else {
                        self.alert(transport.message);
                    }
                }
            });
        },

        // ---------------------------------------

        deleteClick: function (id) {
            this.confirm({
                content: Otto.translator.translate('confirmation_account_delete'),
                actions: {
                    confirm: function () {
                        if (id === undefined) {
                            setLocation(Otto.url.get('deleteAction'));
                        } else {
                            setLocation(Otto.url.get('*/otto_account/delete/', {
                                id: id,
                            }));
                        }
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        // ---------------------------------------

        magentoOrdersListingsModeChange: function () {
            var self = OttoAccountObj;

            if ($('magento_orders_listings_mode').value == 1) {
                $('magento_orders_listings_store_mode_container').show();
            } else {
                $('magento_orders_listings_store_mode_container').hide();
                $('magento_orders_listings_store_mode').value = Otto.php.constant('Account\\Settings\\Order::LISTINGS_STORE_MODE_DEFAULT');
            }

            self.magentoOrdersListingsStoreModeChange();
            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersListingsStoreModeChange: function () {
            if ($('magento_orders_listings_store_mode').value == Otto.php.constant('Account\\Settings\\Order::LISTINGS_STORE_MODE_CUSTOM')) {
                $('magento_orders_listings_store_id_container').show();
            } else {
                $('magento_orders_listings_store_id_container').hide();
                $('magento_orders_listings_store_id').value = '';
            }
        },

        magentoOrdersListingsOtherModeChange: function () {
            var self = OttoAccountObj;

            if ($('magento_orders_listings_other_mode').value == 1) {
                $('magento_orders_listings_other_store_id_container').show();
            } else {
                $('magento_orders_listings_other_store_id_container').hide();
                $('magento_orders_listings_other_store_id').value = '';
            }

            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersNumberChange: function () {
            var self = OttoAccountObj;
            self.renderOrderNumberExample();
        },

        renderOrderNumberExample: function () {
            var orderNumber = '123456789';
            if ($('magento_orders_number_source').value == Otto.php.constant('Account\\Settings\\Order::NUMBER_SOURCE_CHANNEL')) {
                orderNumber = '123412341234123100';
            }

            orderNumber = $('magento_orders_number_prefix_prefix').value + orderNumber;

            $('order_number_example_container').update(orderNumber);
        },

        magentoOrdersCustomerModeChange: function () {
            var customerMode = $('magento_orders_customer_mode').value;

            if (customerMode == Otto.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_PREDEFINED')) {
                $('magento_orders_customer_id_container').show();
                $('magento_orders_customer_id').addClassName('Otto-account-product-id');
            } else {  // Otto.php.constant('Account\Settings\Order::ORDERS_CUSTOMER_MODE_GUEST') || Otto.php.constant('Account\Settings\Order::CUSTOMER_MODE_NEW')
                $('magento_orders_customer_id_container').hide();
                $('magento_orders_customer_id').value = '';
                $('magento_orders_customer_id').removeClassName('Otto-account-product-id');
            }

            var action = (customerMode == Otto.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
            $('magento_orders_customer_new_website_id_container')[action]();
            $('magento_orders_customer_new_group_id_container')[action]();
            $('magento_orders_customer_new_notifications_container')[action]();

            if (action == 'hide') {
                $('magento_orders_customer_new_website_id').value = '';
                $('magento_orders_customer_new_group_id').value = '';
                $('magento_orders_customer_new_notifications').value = '';
            }
        },

        changeVisibilityForOrdersModesRelatedBlocks: function () {
            var self = OttoAccountObj;

            if ($('magento_orders_listings_mode').value == 0 && $('magento_orders_listings_other_mode').value == 0) {

                $('magento_block_otto_accounts_magento_orders_number-wrapper').hide();
                $('magento_orders_number_source').value = Otto.php.constant('Account\\Settings\\Order::NUMBER_SOURCE_MAGENTO');

                $('magento_block_otto_accounts_magento_orders_customer-wrapper').hide();
                $('magento_orders_customer_mode').value = Otto.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_GUEST');
                self.magentoOrdersCustomerModeChange();

                $('magento_block_otto_accounts_magento_orders_rules-wrapper').hide();
                $('magento_orders_qty_reservation_days').value = 1;
                $('magento_block_otto_accounts_magento_orders_tax-wrapper').hide();
                $('magento_orders_customer_billing_address_mode').value = Otto.php.constant('Account\\Settings\\Order::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT');
            } else {
                $('magento_block_otto_accounts_magento_orders_number-wrapper').show();
                $('magento_block_otto_accounts_magento_orders_customer-wrapper').show();
                $('magento_block_otto_accounts_magento_orders_rules-wrapper').show();
                $('magento_block_otto_accounts_magento_orders_tax-wrapper').show();
            }
        },

        // ---------------------------------------

        other_listings_synchronization_change: function () {
            var relatedStoreViews = $('magento_block_otto_accounts_other_listings_related_store_views-wrapper');

            if (this.value == 1) {
                $('other_listings_mapping_mode_tr').show();
                $('other_listings_mapping_mode').simulate('change');
                if (relatedStoreViews) {
                    relatedStoreViews.show();
                }
            } else {
                $('other_listings_mapping_mode').value = 0;
                $('other_listings_mapping_mode').simulate('change');
                $('other_listings_mapping_mode_tr').hide();
                if (relatedStoreViews) {
                    relatedStoreViews.hide();
                }
            }
        },

        other_listings_mapping_mode_change: function () {
            if (this.value == 1) {
                $('magento_block_otto_accounts_other_listings_product_mapping-wrapper').show();
            } else {
                $('magento_block_otto_accounts_other_listings_product_mapping-wrapper').hide();

                $('mapping_sku_mode').value = Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_NONE');
                $('mapping_title_mode').value = Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_TITLE_MODE_NONE');
            }

            $('mapping_sku_mode').simulate('change');
            $('mapping_title_mode').simulate('change');
        },

        synchronization_mapped_change: function () {
            if (this.value == 0) {
                $('settings_button').hide();
            } else {
                $('settings_button').show();
            }
        },

        mapping_sku_mode_change: function () {
            var self = OttoAccountObj,
                    attributeEl = $('mapping_sku_attribute');

            $('mapping_sku_priority').hide();
            if (this.value != Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_NONE')) {
                $('mapping_sku_priority').show();
            }

            attributeEl.value = '';
            if (this.value == Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        mapping_ean_mode_change: function () {
            var self = OttoAccountObj,
                    attributeEl = $('mapping_ean_attribute');

            $('mapping_ean_priority').hide();
            if (this.value != Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_EAN_MODE_NONE')) {
                $('mapping_ean_priority').show();
            }

            attributeEl.value = '';
            if (this.value == Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        mapping_title_mode_change: function () {
            var self = OttoAccountObj,
                    attributeEl = $('mapping_title_attribute');

            $('mapping_title_priority').hide();
            if (this.value != Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_TITLE_MODE_NONE')) {
                $('mapping_title_priority').show();
            }

            attributeEl.value = '';
            if (this.value == Otto.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        openTitlePopup: function () {
            var popup = jQuery('#add_account_form');

            if (popup.find('h1').length === 0) {
                var titleHtml = '<h1 style="margin: 0;">Add Account</h1>';
                popup.prepend(titleHtml);
            }

            modal({
                'type': 'popup',
                'modalClass': 'add-account-popup custom-popup',
                'responsive': true,
                'innerScroll': true,
                'buttons': [],
                'closed': function () {
                    jQuery('#add_account_form').modal('closeModal');
                }
            }, popup);

            popup.modal('openModal');
        },

        // ---------------------------------------
    });

});
