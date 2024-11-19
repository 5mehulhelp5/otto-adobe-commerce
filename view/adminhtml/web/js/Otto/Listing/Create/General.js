define([
    'underscore',
], function (_) {

    window.OttoListingCreateGeneral = Class.create({

        accounts: null,
        selectedAccountId: null,

        // ---------------------------------------

        initialize: function (marketplaces) {
            var self = this;

            CommonObj.setValidationCheckRepetitionValue(
                    'Otto-listing-title',
                    Otto.translator.translate('The specified Title is already used for other Listing. Listing Title must be unique.'),
                    'Listing', 'title', 'id', null
            );

            self.initAccount();
            self.initMarketplace(marketplaces);
        },

        initAccount: function () {
            var self = this;

            $('account_id').observe('change', function () {
                self.selectedAccountId = $('account_id').value || self.selectedAccountId;

                if (_.isNull(self.selectedAccountId)) {
                    return;
                }
            });

            self.renderAccounts();
        },

        renderAccounts: function (callback) {
            let self = this;

            let accountLabelEl = $('account_label');
            let accountSelectEl = $('account_id');

            new Ajax.Request(Otto.url.get('general/getAccounts'), {
                method: 'get',
                onSuccess: function (transport) {
                    var accounts = transport.responseText.evalJSON();

                    if (_.isNull(self.accounts)) {
                        self.accounts = accounts;
                    }

                    if (_.isNull(self.selectedAccountId)) {
                        self.selectedAccountId = $('account_id').value;
                    }

                    var isAccountsChanged = !self.isAccountsEqual(accounts);

                    if (isAccountsChanged) {
                        self.accounts = accounts;
                    }

                    if (accounts.length === 0) {
                        accountLabelEl.update(Otto.translator.translate('Account not found, please create it.'));
                        accountLabelEl.show();
                        accountSelectEl.hide();
                        return;
                    }

                    accountSelectEl.update();
                    accountSelectEl.appendChild(new Element('option', {style: 'display: none'}));
                    accounts.each(function (account) {
                        accountSelectEl.appendChild(new Element('option', {value: account.id})).insert(account.title);
                    });

                    if (accounts.length === 1) {
                        var account = _.first(accounts);

                        $('account_id').value = account.id;
                        self.selectedAccountId = account.id;

                        var accountElement;

                        if (Otto.formData.wizard) {
                            accountElement = new Element('span').update(account.title);
                        } else {
                            var accountLink = Otto.url.get('otto_account/edit', {
                                'id': account.id,
                                close_on_save: 1
                            });
                            accountElement = new Element('a', {
                                'href': accountLink,
                                'target': '_blank'
                            }).update(account.title);
                        }

                        accountLabelEl.update(accountElement);

                        accountLabelEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                        accountSelectEl.hide();
                    } else if (isAccountsChanged) {
                        self.selectedAccountId = _.last(accounts).id;

                        accountLabelEl.hide();
                        accountSelectEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                    }

                    accountSelectEl.setValue(self.selectedAccountId);

                    callback && callback();
                }
            });
        },

        initMarketplace: function () {
            $$('.next_step_button').each(function (btn) {
                btn.observe('click', function () {
                    if (jQuery('#edit_form').valid()) {
                        CommonObj.saveClick(Otto.url.get('otto_listing_create/index'), true);
                    }
                });
            });
        },

        isAccountsEqual: function (newAccounts) {
            if (!newAccounts.length && !this.accounts.length) {
                return true;
            }

            if (newAccounts.length !== this.accounts.length) {
                return false;
            }

            return _.every(this.accounts, function (account) {
                return _.where(newAccounts, account).length > 0;
            });
        }

        // ---------------------------------------
    });
});
