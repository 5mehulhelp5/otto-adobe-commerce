define([
    'Otto/Common'
], function () {
    window.OttoSettingsMain = Class.create(Common, {

        initialize: function () {
            var self = this

            jQuery.validator.addMethod('validator-required-when-visible', function (value, el) {
                return value > 0
            }, 'This is a required field.')
        },

        identifier_code_mode_change: function () {
            var self = OttoSettingsMainObj;

            $('identifier_code_custom_attribute').value = '';
            if (this.value == Otto.php.constant('M2E_Otto_Helper_Component_Otto_Configuration::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, $('identifier_code_custom_attribute'));
            }
        },
    })
})
