define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Otto/Plugin/Messages',
    'mage/translate',
    'Otto/Common',
    'Otto/Plugin/Magento/AttributeCreator'
], function (jQuery, modal, MessageObj, $t) {
    window.OttoCategoryChooser = Class.create(Common, {

        // ---------------------------------------

        accountId: null,

        categoryInfoBlockMessages: null,
        categoryChangeBlockMessages: null,

        selectedCategory: {},
        selectedSpecifics: {},


        // ---------------------------------------

        initialize: function (accountId) {
            this.accountId = accountId;

            this.categoryInfoBlockMessages = Object.create(MessageObj);
            this.categoryInfoBlockMessages.setContainer('#category_info_messaged_container');

            this.categoryChangeBlockMessages = Object.create(MessageObj);
            this.categoryChangeBlockMessages.setContainer('#change_category_messaged_container');
        },

        initObservers: function () {
            const self = this

            jQuery('#edit_category').on('click',function () {
                self.showEditCategoryPopUp();
            });

            jQuery('#edit_attributes').on('click',function () {
                self.editAttributes();
            });
        },

        // ---------------------------------------

        getAccountId: function () {
            return this.accountId;
        },

        setSelectedCategory: function (category) {
            this.selectedCategory.categoryId = category;
        },

        getSelectedCategory: function () {
            if (this.isCategorySelected()) {
                return this.selectedCategory;
            }

            return {
                value: '',
                path: '',
            };
        },

        // ---------------------------------------

        showEditCategoryPopUp: function () {
            this.messagesClearAll();
            const self = this;
            const selected = self.getSelectedCategory();

            new Ajax.Request(Otto.url.get('otto_category/getChooserEditHtml'), {
                method: 'post',
                parameters: {
                    account_id: self.accountId,
                    selected_value: selected.value,
                    selected_path: selected.path,
                    view_mode: 'with_tabs',
                },
                onSuccess: function (transport) {
                    self.openPopUp(Otto.translator.translate('Change Category'), transport.responseText);
                    self.renderRecent();

                    let categoryPathElement = $('selected_category_container').down('#selected_category_path');
                    categoryPathElement.innerHTML = self.cutDownLongPath(categoryPathElement.innerHTML.trim(), 130, '&gt;');
                }
            });
        },

        openPopUp: function (title, html) {
            const self = this;
            let chooserContainer = $('chooser_container');

            if (chooserContainer) {
                chooserContainer.remove();
            }

            $('html-body').insert({bottom: html});

            jQuery('#category_search').applyBindings();

            let content = jQuery('#chooser_container');

            modal({
                title: title,
                type: 'slide',
                buttons: [{
                    class: 'template_category_chooser_cancel',
                    text: Otto.translator.translate('Cancel'),
                    click: function () {
                        self.cancelPopUp();
                    }
                }, {
                    class: 'action primary template_category_chooser_confirm',
                    text: Otto.translator.translate('Confirm'),
                    click: function () {
                        self.confirmCategory();
                    }
                }]
            }, content);

            content.modal('openModal');
        },

        // ---------------------------------------

        cancelPopUp: function () {
            jQuery('#chooser_container').modal('closeModal');
        },

        // ---------------------------------------

        selectExistCategory: function (categoryId, categoryTitle) {
            this.messagesClearAll();
            const self = this;
            self.selectedCategory = {
                categoryId: categoryId
            };
            self.updateCategoryTitleField(categoryTitle);
        },

        selectCategory: function (categoryDictionaryId, categoryGroupId, categoryTitle) {
            this.messagesClearAll();
            const self = this;
            self.selectedCategory = {
                categoryDictionaryId: categoryDictionaryId,
                categoryGroupId: categoryGroupId,
                path: categoryTitle
            };
            self.updateCategoryTitleField(categoryTitle);
        },

        updateCategoryTitleField: function (categoryTitle) {
            let pathElement = $('selected_category_path');
            if (pathElement) {
                pathElement.setAttribute('title', categoryTitle);
                pathElement.innerHTML = categoryTitle;
            }

            let resetLink = $('category_reset_link');
            if (resetLink) {
                resetLink.show();
            }
        },


        unSelectCategory: function () {

            this.selectedCategory = {};

            let selectedCategoryPath = $('selected_category_path'),
                    resetLink = $('category_reset_link');

            if (resetLink) {
                resetLink.hide();
            }

            if (selectedCategoryPath) {
                selectedCategoryPath.innerHTML = '<span style="color: grey; font-style: italic">'
                        + Otto.translator.translate('Not Selected')
                        + '</span>';
            }
        },

        confirmCategory: function () {
            const self = this;

            self.messagesClearAll();

            if (self.isCategorySelected() && self.selectedCategory.categoryDictionaryId
                || self.selectedCategory.categoryId) {
                new Ajax.Request(Otto.url.get('otto_category/getEditedCategoryInfo'), {
                    method: 'post',
                    parameters: {
                        category_group_id: self.selectedCategory.categoryGroupId,
                        category_dictionary_id: self.selectedCategory.categoryDictionaryId,
                        category_id:self.selectedCategory.categoryId,
                        title: self.selectedCategory.path
                    },
                    onSuccess: function (transport) {
                        let response = transport.responseText.evalJSON();

                        if (response.hasOwnProperty('success') && !response.success) {
                            self.messageAddErrorToCategoryInfoBlock(response.message);

                            return;
                        }

                        self.selectedCategory.categoryId = response.category_id;
                        self.selectedCategory.categoryGroupId = response.category_group_id;
                        self.selectedCategory.categoryDictionaryId = response.category_dictionary_id;
                        self.selectedCategory.is_all_required_attributes_filled = response.is_all_required_attributes_filled;
                        self.selectedCategory.path = response.title;
                        self.selectedCategory.has_required_attributes = response.has_required_attributes

                        self.reload();
                    }
                });
            } else {
                self.messageAddErrorToCategoryChangeBlock($t('Please select a category to continue.'));

                return;
            }

            jQuery('#chooser_container').modal('closeModal');
        },

        reload: function () {
            if (!this.isCategorySelected()) {
                jQuery('#category_path').text('Not selected')
                this.unsetIsRequiredAttributes();
                jQuery('#attributes_wrapper').addClass('hidden');

                return;
            }

            if (this.selectedCategory.path) {
                jQuery('#category_path').text(this.selectedCategory.path)
                jQuery('#attributes_wrapper').removeClass('hidden');
            }

            if (this.selectedCategory.categoryGroupId) {
                this.getCountsOfSpecifics(this.selectedCategory.categoryGroupId, this.selectedCategory.categoryId, function (used, total) {
                    jQuery('#attributes_counts').text(used + '/' + total);
                });
            } else {
                jQuery('#attributes_counts').text('0/0');
            }

            if (this.selectedCategory.is_all_required_attributes_filled) {
                this.unsetIsRequiredAttributes();
                return;
            }

            if (this.selectedCategory.is_all_required_attributes_filled) {
                this.unsetIsRequiredAttributes();
            } else {
                this.setIsRequiredAttributes();
            }
        },

        setIsRequiredAttributes: function () {
            jQuery('#attributes_required').removeClass('hidden')
        },

        unsetIsRequiredAttributes: function () {
            jQuery('#attributes_required').addClass('hidden');
        },

        attributesIsRequired: function () {
            return !jQuery('#attributes_required').hasClass('hidden');
        },

        isCategorySelected: function () {
            return Object.keys(this.selectedCategory).length !== 0;
        },

        isSpecificsSelected: function () {
            return Object.keys(this.selectedSpecifics).length !== 0;
        },

        getCountsOfSpecifics: function (categoryGroupId, categoryId, callback) {
            new Ajax.Request(Otto.url.get('otto_category/getCountsOfAttributes'), {
                method: 'post',
                parameters: {
                    category_group_id: categoryGroupId,
                    category_id: categoryId
                },
                onSuccess: function (transport) {
                    const counts = transport.responseText.evalJSON();
                    callback(counts.used, counts.total);
                }
            });
        },

        // ---------------------------------------

        renderRecent: function () {
            const self = this;

            if (!$('chooser_recent_table')) {
                return;
            }

            new Ajax.Request(Otto.url.get('otto_category/getRecent'), {
                method: 'post',
                parameters: {
                    selected_category: null
                },
                onSuccess: function (transport) {
                    const categories = transport.responseText.evalJSON();
                    let html = '';
                    if (transport.responseText.length > 2) {
                        html += '<tr><td width="730px"></td><td width="70px"></td></tr>';
                        categories.each(function (category) {
                            html += '<tr><td>' + category.title + '</td>' +
                                    '<td style="width: 60px"><a href="javascript:void(0)" ' +
                                    'onclick="OttoCategoryChooserObj.selectExistCategory(\'' + category.id + '\', \'' + category.title + '\')">' +
                                    Otto.translator.translate('Select') + '</a></td></tr>';
                        });
                    } else {
                        html += '<tr><td colspan="2" style="padding-left: 200px"><strong>' + Otto.translator.translate('No saved Categories') + '</strong></td></tr>';
                    }

                    $('chooser_recent_table').innerHTML = html;
                }
            });
        },

        // ---------------------------------------

        cutDownLongPath: function (path, length, sep) {
            if (path.length > length && sep) {

                var parts = path.split(sep),
                        isNeedSeparator = false;

                var shortPath = '';
                parts.each(function (part, index) {
                    if ((part.length + shortPath.length) >= length) {

                        var lenDiff = (parts[parts.length - 1].length + shortPath.length) - length;
                        if (lenDiff > 0) {
                            shortPath = shortPath.slice(0, shortPath.length - lenDiff + 1);
                        }

                        shortPath = shortPath.slice(0, shortPath.length - 3) + '...';

                        shortPath += parts[parts.length - 1];
                        throw $break;
                    }

                    shortPath += part + (isNeedSeparator ? sep : '');
                    isNeedSeparator = true;
                });

                return shortPath;
            }

            return path;
        },

        // ---------------------------------------

        editAttributes: function () {
            const self = this;

            self.messagesClearAll();

            let selectedCategory = this.getSelectedCategory();

            new Ajax.Request(Otto.url.get('otto_category/getCategoryAttributesHtml'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    category_group_id: selectedCategory.categoryGroupId,
                    category_id: selectedCategory.categoryId,
                    title: self.selectedCategory.path
                },
                onSuccess: function (transport) {
                    self.openSpecificsPopUp(Otto.translator.translate('Specifics'), transport.responseText);
                }.bind(this)
            });
        },

        openSpecificsPopUp: function (title, html) {
            const self = this;
            if ($('chooser_container_specific')) {
                $('chooser_container_specific').remove();
            }

            $('html-body').insert({bottom: html});

            const content = jQuery('#chooser_container_specific');

            modal({
                title: title,
                type: 'slide',
                buttons: [{
                    class: 'template_category_specific_chooser_cancel',
                    text: Otto.translator.translate('Cancel'),
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    class: 'action primary template_category_specific_chooser_reset',
                    text: Otto.translator.translate('Reset'),
                    click: function () {
                        OttoTemplateCategorySpecificsObj.resetSpecifics();
                    }
                }, {
                    class: 'action primary template_category_specific_chooser_save',
                    text: Otto.translator.translate('Save'),
                    click: function () {
                        self.confirmSpecifics();
                    }
                }]
            }, content);

            content.modal('openModal');
        },

        confirmSpecifics: function () {
            this.initFormValidation('#edit_specifics_form');
            if (!jQuery('#edit_specifics_form').valid()) {
                return;
            }

            const self = OttoCategoryChooserObj;

            this.selectedSpecifics = OttoTemplateCategorySpecificsObj.collectSpecifics();

            new Ajax.Request(Otto.url.get('otto_category/saveCategoryDataAjax'), {
                method: 'post',
                parameters: {
                    category: JSON.stringify(self.selectedCategory),
                    attributes: JSON.stringify(this.selectedSpecifics),
                },
                onSuccess: function (transport) {

                    const response = transport.responseText.evalJSON();

                    self.messagesClearAll();
                    if (response.success) {
                        self.selectedCategory.is_all_required_attributes_filled = true;
                        self.selectedCategory.categoryId = response.category_id;
                        self.messageAddSuccessToCategoryInfoBlock($t('Attributes was saved'));
                    } else {
                        self.messageAddErrorToCategoryInfoBlock($t('Attributes not saved'));
                    }

                    self.reload();
                    jQuery('#chooser_container_specific').modal('closeModal');
                }
            });
        },

        resetSpecificsToDefault: function () {
            const self = OttoCategoryChooserObj,
                    selectedCategory = this.getSelectedCategory();

            new Ajax.Request(Otto.url.get('otto_category/getSelectedCategoryDetails'), {
                method: 'post',
                parameters: {
                    value: selectedCategory.value,
                },
                onSuccess: function (transport) {

                    self.selectedSpecifics = {};

                    self.reload();
                }
            });
        },

        // ----------------------------------------

        messagesClearAll: function () {
            this.messagesClearOnCategoryInfoBlock();
            this.messagesClearOnCategoryChangeBlock();
        },

        // ----------------------------------------

        messagesClearOnCategoryInfoBlock: function () {
            this.categoryInfoBlockMessages.clearAll();
        },

        messageAddErrorToCategoryInfoBlock: function (message) {
            this.messagesClearOnCategoryInfoBlock();
            this.categoryInfoBlockMessages.addError(message);
        },

        messageAddSuccessToCategoryInfoBlock: function (message) {
            this.messagesClearOnCategoryInfoBlock();
            this.categoryInfoBlockMessages.addSuccess(message);
        },

        messagesClearOnCategoryChangeBlock: function (message) {
            this.categoryChangeBlockMessages.clearAll();
        },

        messageAddErrorToCategoryChangeBlock: function (message) {
            this.messagesClearOnCategoryChangeBlock();
            this.categoryChangeBlockMessages.addError(message);
        }
    });
});
