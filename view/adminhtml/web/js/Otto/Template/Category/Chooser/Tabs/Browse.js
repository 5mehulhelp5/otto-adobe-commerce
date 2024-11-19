define([
    'jquery',
    'Otto/Common'
], function (jQuery) {
    window.OttoTemplateCategoryChooserTabsBrowse = Class.create(Common, {

        categoryGroupList: [],

        // ---------------------------------------

        initialize: function () {
            this.accountId = null;
            this.observers = {
                "leaf_selected": [],
                "not_leaf_selected": [],
                "any_selected": []
            };
        },

        //----------------------------------------

        setAccountId: function (accountId) {
            this.accountId = accountId;
        },

        getAccountId: function () {
            if (this.accountId === null) {
                alert('You must set Account');
            }

            return this.accountId;
        },

        //----------------------------------------

        getCategoriesSelectElementId: function (categoryGroupId) {
            if (categoryGroupId === null) categoryGroupId = 0;
            return 'category_chooser_select_' + categoryGroupId;
        },

        getCategoryChildrenElementId: function (categoryGroupId) {
            if (categoryGroupId === null) categoryGroupId = 0;
            return 'category_chooser_children_' + categoryGroupId;
        },

        getSelectedCategories: function () {
            var self = OttoTemplateCategoryChooserTabsBrowseObj;

            var categoryGroupId = 0;
            var selectedCategories = [];
            var isLastCategory = false;
            var categoryGroupIdLeaf = 0;
            var categoryDictionaryId = 0;
            var title = '';

            while (!isLastCategory) {
                var categorySelect = $(self.getCategoriesSelectElementId(categoryGroupId));
                if (!categorySelect || categorySelect.selectedIndex == -1) {
                    break;
                }

                const isLeaf = categorySelect.options[categorySelect.selectedIndex].getAttribute('is_leaf');
                if (isLeaf == 1) {
                    categoryGroupIdLeaf = categorySelect.options[categorySelect.selectedIndex].getAttribute('category_group_id');
                    categoryDictionaryId = categorySelect.options[categorySelect.selectedIndex].value;
                    title = categorySelect.options[categorySelect.selectedIndex].text;
                    selectedCategories[selectedCategories.length] = {
                        'category_group_id': categoryGroupIdLeaf,
                        'category_dictionary_id': categoryDictionaryId,
                        'title': title,
                    }

                    isLastCategory = true;
                } else {
                    categoryGroupId = categorySelect.options[categorySelect.selectedIndex].value;
                    selectedCategories[selectedCategories.length] = {
                        'value': categoryGroupId
                    }
                }
            }

            return selectedCategories;
        },

        // ---------------------------------------

        showCategoryGroups: function (containerId) {
            this.prepareDomStructure(null, $(containerId));
            this.renderCategoryGroups();
        },

        renderCategoryGroups: function () {
            let self = this;

            new Ajax.Request(Otto.url.get('otto_category/getCategoryGroups'), {
                method: 'post',
                asynchronous: true,
                onSuccess: function (transport) {

                    if (transport.responseText.length <= 2) {
                        return;
                    }

                    let categoryGroups = JSON.parse(transport.responseText);
                    self.categoryGroupList = categoryGroups;
                    let optionsHtml = '';
                    categoryGroups.each(function (categoryGroup) {
                        let title   = categoryGroup.title

                        optionsHtml += `<option value="${categoryGroup.category_group_id}">`;
                        optionsHtml += title + ' >';
                        optionsHtml += '</option>';
                    });

                    $(self.getCategoriesSelectElementId(null)).innerHTML = optionsHtml;
                    $(self.getCategoriesSelectElementId(null)).style.display = 'inline-block';

                    $('chooser_browser').scrollLeft = $('chooser_browser').scrollWidth;
                }
            });
        },

        renderChildCategories: function (categoryGroupId) {
            let self = this;

            new Ajax.Request(Otto.url.get('otto_category/getCategories'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    "category_group_id": categoryGroupId
                },
                onSuccess: function (transport) {

                    if (transport.responseText.length <= 2) {
                        return;
                    }

                    let categories = JSON.parse(transport.responseText);
                    let optionsHtml = '';
                    categories.each(function (category) {
                        let title = category.title;

                        optionsHtml += `<option category_group_id = "${categoryGroupId}" is_leaf="1" value="${category.category_dictionary_id}">`;
                        optionsHtml += title;
                        optionsHtml += '</option>';
                    });

                    $(self.getCategoriesSelectElementId(categoryGroupId)).innerHTML = optionsHtml;
                    $(self.getCategoriesSelectElementId(categoryGroupId)).style.display = 'inline-block';

                    $('chooser_browser').scrollLeft = $('chooser_browser').scrollWidth;
                }
            });
        },

        onSelectCategory: function (select) {
            var self = OttoTemplateCategoryChooserTabsBrowseObj;

            var parentCategoryGroupId = select.id.replace(self.getCategoriesSelectElementId(""), "");
            var categoryGroupId = select.options[select.selectedIndex].value;
            var is_leaf = select.options[select.selectedIndex].getAttribute('is_leaf');

            var selectedCategories = self.getSelectedCategories();

            var parentDiv = $(self.getCategoryChildrenElementId(parentCategoryGroupId));
            parentDiv.innerHTML = '';

            self.simulate('any_selected', selectedCategories);

            if (is_leaf == 1) {
                self.simulate('leaf_selected', selectedCategories);
                return;
            }

            self.simulate('not_leaf_selected', selectedCategories);

            self.prepareDomStructure(categoryGroupId, parentDiv);
            self.renderChildCategories(categoryGroupId);
        },

        prepareDomStructure: function (categoryGroupId, parentDiv) {
            var self = OttoTemplateCategoryChooserTabsBrowseObj;

            var childrenSelect = document.createElement('select');
            childrenSelect.id = self.getCategoriesSelectElementId(categoryGroupId);
            childrenSelect.style.minWidth = '200px';
            childrenSelect.style.maxHeight = 'none';
            childrenSelect.size = 10;
            childrenSelect.className = 'multiselect admin__control-multiselect';
            childrenSelect.onchange = function () {
                OttoTemplateCategoryChooserTabsBrowseObj.onSelectCategory(this);
            };
            childrenSelect.style.display = 'none';
            parentDiv.appendChild(childrenSelect);

            var childrenDiv = document.createElement('div');
            childrenDiv.id = self.getCategoryChildrenElementId(categoryGroupId);
            childrenDiv.className = 'category-children-block';
            parentDiv.appendChild(childrenDiv);
        },

        applySearchFilter: function ()
        {
            const title = $('category_search_query').value;
            const categoryGroups = this.searchCategoryGroupsByTitle(title);

            const container = $('category_chooser_select_0');
            container.innerHTML = '';

            for (let i = 0; i < categoryGroups.length; i++) {
                this.insertOption(container, categoryGroups[i].category_group_id, categoryGroups[i].title + ' >');
            }
        },

        resetSearchFilter: function ()
        {
            $('category_search_query').value = '';

            const childCategoriesContainer = jQuery('[id^="category_chooser_children_"]').find('[id^="category_chooser_select_"]');
            childCategoriesContainer.remove();

            this.applySearchFilter();
        },

        searchCategoryGroupsByTitle: function (title)
        {
            if (!title) {
                return this.categoryGroupList.clone();
            }

            const titleLowerCase = title.toLowerCase();
            return this.categoryGroupList.filter(
                    function (value) {
                        return value.title
                                .toLowerCase()
                                .indexOf(titleLowerCase) !== -1;
                    }
            );
        },

        insertOption: function (container, value, title) {
            const option = new Element('option', {value: value});
            option.innerHTML = title;
            container.appendChild(option);
        },

        // ---------------------------------------

        observe: function (event, observer) {
            var self = OttoTemplateCategoryChooserTabsBrowseObj;

            if (typeof observer != 'function') {
                self.alert('Observer must be a function!');
                return;
            }

            if (typeof self.observers[event] == 'undefined') {
                self.alert('Event does not supported!');
                return;
            }

            self.observers[event][self.observers[event].length] = observer;
        },

        simulate: function (event, parameters) {
            var self = OttoTemplateCategoryChooserTabsBrowseObj;

            parameters = parameters || null;

            if (typeof self.observers[event] == 'undefined') {
                self.alert('Event does not supported!');
                return;
            }

            if (self.observers[event].length == 0) {
                return;
            }

            self.observers[event].each(function (observer) {
                if (parameters == null) {
                    (observer)();
                } else {
                    (observer)(parameters);
                }
            });
        }

        // ---------------------------------------
    });
});
