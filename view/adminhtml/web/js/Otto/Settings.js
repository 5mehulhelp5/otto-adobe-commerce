define([
    'jquery',
    'mage/translate',
    'Otto/Plugin/Confirm',
    'Otto/Settings',
], ($, $t, confirm) => {
    window.OttoSettings = Class.create(Settings, {
        urlSetGpsrToCategory: null,

        initialize: function($super, urlSetGpsrToCategory) {
            $super();
            this.urlSetGpsrToCategory = urlSetGpsrToCategory;
        },

        afterSaveSettings: function(tabId, response) {
            if (tabId === 'mapping') { // Settings\Tabs::TAB_ID_MAPPING_ATTRIBUTES
                this.afterMappingProcess(response);
            }
        },

        afterMappingProcess: function(response) {
            if (!response.success) {
                return;
            }

            if (response.was_changed_gpsr) {
                this.gpsrHandle();

                return;
            }
        },

        gpsrHandle: function() {
            confirm(
                    {
                        title: $t('New Attribute Mapping Detected'),
                        content: $t('You\'ve successfully saved the default attribute mapping settings for Otto GPSR attributes. Would you like to apply these new default mappings to all your Otto categories now?'),
                        actions: {
                            confirm: () => {
                                this.gpsrSetToCategories(this.urlSetGpsrToCategory);
                            },
                            cancel: () => {},
                        },
                    },
            );
        },

        gpsrSetToCategories: function(url) {
            const self = this;

            new Ajax.Request(url, {
                method: 'post',
                asynchronous: true,
                parameters: {},
                onSuccess: function(transport) {
                    const response = transport.responseText;

                    if (!response.isJSON()) {
                        self.writeMessage(response, false);

                        return;
                    }

                    const result = JSON.parse(response);
                    if (!result.success) {
                        self.messageObj.addError($t('Error'));
                    }
                }
            });
        }
    });
});
