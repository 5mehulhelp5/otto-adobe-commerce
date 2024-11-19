define([
    'jquery',
    'Otto/Url',
    'Otto/Php',
    'Otto/Translator',
    'Otto/Common',
    'prototype',
    'Otto/Plugin/BlockNotice',
    'Otto/Plugin/Prototype/Event.Simulate',
    'Otto/Plugin/Fieldset',
    'Otto/Plugin/Validator',
    'Otto/General/PhpFunctions',
    'mage/loader_old'
], function (jQuery, Url, Php, Translator) {

    jQuery('body').loader();

    Ajax.Responders.register({
        onException: function (event, error) {
            console.error(error);
        }
    });

    return {
        url: Url,
        php: Php,
        translator: Translator
    };

});
