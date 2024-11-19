define([
    'jquery',
    'mage/translate',
    'Otto/Common'
], function ($, $t) {
    window.OttoTemplateCategoryChooserTabsSearch = Class.create(Common, {

        categoryChooser: null,
        searchContainer: null,
        resultContainer: null,

        /**
         * @param {OttoCategoryChooser} categoryChooser
         * @param {string} searchContainerId
         * @param {string} resultContainerId
         */
        initialize: function (categoryChooser, searchContainerId, resultContainerId) {
            this.categoryChooser = categoryChooser
            this.searchContainer = $('#' + searchContainerId)
            this.resultContainer = $('#' + resultContainerId)

            this.initObservers()
        },

        initObservers: function () {
            const self = this;

            this.searchContainer.on('click', '.reset-input', function () {
                self.searchContainer.find('.search-input').val('').focus()
                self.resultContainer.find('.search_results_table').empty()
            })

            this.searchContainer.on('click', '.search-btn', function () {
                self.search(self.searchContainer.find('.search-input').val())
            })

            this.searchContainer.on('keypress', '.search-input', function (event) {
                if (event.which !== 13) {
                    return;
                }

                self.search(self.searchContainer.find('.search-input').val())
            })

            this.resultContainer.on('click', '.choice-category', function (event) {
                const choiceLink = $(event.currentTarget);

                const categoryDictionaryId = choiceLink.attr('data-category-id');
                const categoryGroupId = choiceLink.attr('data-category-group-id');
                const title = choiceLink.attr('data-category-title');

                self.categoryChooser.selectCategory(categoryDictionaryId, categoryGroupId, title);
            })
        },

        search: function (searchQuery) {
            const self = this;

            new Ajax.Request(Otto.url.get('otto_category/search'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    "search_query": searchQuery
                },
                onSuccess: function (transport) {
                    const resultTable = self.resultContainer.find('.search_results_table');
                    resultTable.empty()

                    $.each(transport.responseText.evalJSON(), function (index, category) {

                        let categoryName = `${category['path']}`;
                        let style = '';
                        let choiceLink = `<a class="choice-category"
                                                    data-category-id="${category['id']}"
                                                    data-category-group-id="${category['category_group_id']}"
                                                    data-category-title="${category['path']}">
                                                    ${$t('Select')}
                                                 </a>`

                        const row = `
                            <tr>
                                <td><span style="${style}">${categoryName}</span></td>
                                <td style="text-align: right">${choiceLink}</td>
                            </tr>
                        `
                        resultTable.append($(row))
                    });
                }
            });
        }
    });
});
