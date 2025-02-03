define([
    'Otto/Product/Unmanaged/Move/RetrieveSelected',
    'Otto/Product/Unmanaged/Move/PrepareProducts',
    'Otto/Product/Unmanaged/Move/Processor',
], (RetrieveSelected, PrepareProducts, MoveProcess) => {
    'use strict';

    return {
        startMoveForProduct: (id, urlPrepareMove, urlGrid, urlListingCreate, accountId) => {
            PrepareProducts.prepareProducts(
                    urlPrepareMove,
                    [id],
                    accountId,
                    function () {
                        MoveProcess.openMoveToListingGrid(
                                urlGrid,
                                urlListingCreate,
                                accountId
                        );
                    }
            );
        },

        startMoveForProducts: (massActionData, urlPrepareMove, urlGrid, urlGetSelectedProducts, urlListingCreate, accountId) => {
            RetrieveSelected.getSelectedProductIds(
                    massActionData,
                    urlGetSelectedProducts,
                    accountId,
                    function (selectedProductIds) {
                        PrepareProducts.prepareProducts(
                                urlPrepareMove,
                                selectedProductIds,
                                accountId,
                                function () {
                                    MoveProcess.openMoveToListingGrid(
                                            urlGrid,
                                            urlListingCreate,
                                            accountId
                                    );
                                }
                        );
                    }
            );
        }
    };
});
