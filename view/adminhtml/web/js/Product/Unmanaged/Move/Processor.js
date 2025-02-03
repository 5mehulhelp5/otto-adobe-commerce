define([
    'jquery',
    'mage/translate',
], ($, $t) => {
    'use strict';

    return {
        openMoveToListingGrid: function (urlGrid, urlListingCreate, accountId) {
            const self = this;
            $.ajax(
                    {
                        url: urlGrid,
                        type: 'GET',
                        data: {
                            account_id: accountId
                        },
                        success: function(data) {
                            self.openListingPopUp(urlListingCreate, accountId, data, $t('Moving Otto Items'));
                        },
                    }
            );
        },

        openListingPopUp: function (urlListingCreate, accountId, gridHtml, popup_title, buttons) {
            const self = this;

            if (typeof buttons === 'undefined') {
                buttons = [{
                    class: 'action-secondary action-dismiss',
                    text: ('Cancel'),
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $t('Add New Listing'),
                    class: 'action-primary action-accept',
                    click: function () {
                        self.startListingCreation(urlListingCreate, accountId);
                    }
                }];
            }

            let modalDialogMessage = $('move_modal_dialog_message');

            if (modalDialogMessage) {
                modalDialogMessage.remove();
            }

            modalDialogMessage = new Element('div', {
                id: 'move_modal_dialog_message'
            });

            modalDialogMessage.update(gridHtml);

            this.popUp = $(modalDialogMessage).modal({
                title: popup_title,
                type: 'popup',
                buttons: buttons
            });

            this.popUp.modal('openModal');
        },

        startListingCreation: function (urlListingCreate, accountId) {
            const step = 1;
            const creationMode = 1;
            const urlListingCreateNew = `${urlListingCreate}step/${step}/account_id/${accountId}/creation_mode/${creationMode}/`;
            let win = window.open(urlListingCreateNew);

            let intervalId = setInterval(function () {
                if (!win.closed) {
                    return;
                }

                clearInterval(intervalId);

                listingMovingGridJsObject.reload();
            }, 1000);
        }
    };
});
