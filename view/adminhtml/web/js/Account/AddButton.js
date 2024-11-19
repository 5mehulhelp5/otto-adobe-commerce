define([
    'Magento_Ui/js/modal/modal',
    'jquery',
], function (modal, $) {
    'use strict';

    return function (options) {
        const addAccountPopup = $('.custom-popup');

        $('#add_account').on('click', function () {
            modal({
                type: 'popup',
                buttons: []
            }, addAccountPopup);

            addModeField(options.mode);

            addAccountPopup.modal('openModal');
        });

        function addModeField(mode) {
            const hiddenField = $('<input>').attr({
                type: 'hidden',
                id: 'mode',
                name: 'mode',
                value: mode
            });

            $('#account_credentials').append(hiddenField);
        }

        $('#account_credentials').on('submit', function (e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: options.urlCreate + 'extension',
                type: 'POST',
                data: formData,
                showLoader: true,
                dataType: 'json',
                success: function (response) {
                    addAccountPopup.modal('closeModal');

                    if (response.redirectUrl) {
                        setLocation(response.redirectUrl);
                    }
                }
            });
        });
    }
});
