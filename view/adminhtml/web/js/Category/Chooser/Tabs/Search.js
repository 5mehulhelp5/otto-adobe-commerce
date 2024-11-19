define([
    'uiElement',
    'mage/translate',
    'Otto/Category/Chooser'
], (uiElement, $t) => {
    'use strict';

    return uiElement.extend({
        queryMinChars: 3,

        defaults: {
            query: '',
            foundCategories: [],
            searched: false,
            hasMoreCategories: false,
            hasFoundCategories: false,
            searchUrl: '',
            chooserManager: window.OttoCategoryChooserObj,
            tracks: {
                'foundCategories': true,
                'query': true,
                'hasMoreCategories': true,
                'searched': true,
                'hasFoundCategories': true
            },
        },

        // ----------------------------------------

        reset() {
            this.query = '';
            this.foundCategories = [];
            this.searched = false;
            this.hasFoundCategories = false;
            this.hasMoreCategories = false;
            this.chooserManager.messagesClearOnCategoryChangeBlock();
        },

        search() {
            if (this.query.length < this.queryMinChars) {
                this.chooserManager.messageAddErrorToCategoryChangeBlock(
                        $t('The search query is too short. Please enter at least 3 characters.')
                );

                return;
            }

            this.chooserManager.messagesClearOnCategoryChangeBlock();

            this.doSearch(this.query);
        },

        searchOnEnter(uiElement, event) {
            if (event.which !== 13) {
                return true;
            }

            this.search();
        },

        selectCategory(categoryDictionaryId, categoryGroupId, title) {
            this.chooserManager.selectCategory(categoryDictionaryId, categoryGroupId, title);
        },

        // ----------------------------------------

        doSearch(query) {
            new Ajax.Request(this.searchUrl, {
                method: 'post',
                asynchronous: true,
                parameters: {
                    'search_query': query,
                },
                onSuccess: this.processCategories.bind(this),
            });
        },

        processCategories(transport) {
            this.foundCategories = [];

            const response = transport.responseText.evalJSON();

            response.categories.forEach(
                    (categoryData) => {
                        this.foundCategories.push(
                                {
                                    'id': categoryData.id,
                                    'category_group_id': categoryData.category_group_id,
                                    'name': categoryData.path,
                                },
                        );
                    },
            );
            console.log(this.foundCategories);
            this.hasMoreCategories = response.has_more;
            this.hasFoundCategories = this.foundCategories.length > 0;
            this.searched = true;
        },
    });
});
