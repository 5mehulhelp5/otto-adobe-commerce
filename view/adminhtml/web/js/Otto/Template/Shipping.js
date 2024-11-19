define([
    'jquery',
    'mage/translate',
    'Otto/Common',
    'Magento_Ui/js/modal/confirm'
], function ($, $t) {

    window.OttoTemplateShipping = Class.create(Common, {

        initialize: function () {
            $.validator.addMethod('Otto-validate-handling-time-mode', function (value, element) {
                const el = $(element);
                if ($('#handling_time').val() == 0 && el.val() == Otto.php.constant('\\M2E\\Otto\\Model\\Template\\Shipping::HANDLING_TIME_MODE_VALUE')) {
                    return false;
                }

                return true;
            }, $t('This is a required field.'));

            $.validator.addMethod('Otto-validate-time', function (value) {
                return /^([01]\d|2[0-3]):(00|30)$/.test(value);
            }, $t('Please enter a valid time in HH:MM format.'));

        },

        initObservers: function () {
            $('#handling_time_mode')
                    .on('change', this.handlingTimeChange.bind(this))
                    .trigger('change');
        },

        handlingTimeChange: function (event)
        {
            const el = event.target;

            if (el.value == Otto.php.constant('\\M2E\\Otto\\Model\\Template\\Shipping::HANDLING_TIME_MODE_VALUE')) {
                this.updateHiddenValue(el, $('#handling_time')[0]);
            }
        },
    });
});
