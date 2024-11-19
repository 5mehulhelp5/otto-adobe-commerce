define([
    'Otto/Plugin/RandomColor',
    'Otto/Plugin/Messages'
], function (RandomColor, MessagesObj) {

    window.LogView = Class.create(Common, {

        notificationWasAdded: false,

        processColorMapping: function () {

            if (
                    !LogViewObj.notificationWasAdded
                    && !_.isUndefined(Otto.formData.maxAllowedLogsCountExceededNotification)
            ) {
                LogViewObj.notificationWasAdded = true;
                MessagesObj.addNotice(Otto.formData.maxAllowedLogsCountExceededNotification);
            }

            jQuery('.data-grid tbody tr:not(.data-grid-tr-no-data)').each(function () {

                var row = jQuery(this);
                var logHash = row.find('.log-hash').text().trim();

                if (!logHash.length) {
                    return;
                }

                var color = RandomColor({
                    seed: +logHash
                });

                row.find('td:first').css({
                    borderLeftWidth: '7px',
                    borderLeftColor: '#' + color
                });
            });
        }
    });

});
