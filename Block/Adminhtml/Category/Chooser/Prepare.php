<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Category\Chooser;

class Prepare extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    private \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('category/chooser/prepare.phtml');

        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(\M2E\Otto\Model\Otto\Template\Category::class),
        );

        $urlBuilder = $this->_urlBuilder;

        $this->jsUrl->addUrls(
            [
                'otto_category/editCategory' => $urlBuilder->getUrl(
                    '*/otto_category/editCategory'
                ),
                'otto_category/getCategoryAttributesHtml' => $urlBuilder->getUrl(
                    '*/otto_category/getCategoryAttributesHtml'
                ),
                'otto_category/getCategoryGroups' => $urlBuilder->getUrl(
                    '*/otto_category/getCategoryGroups'
                ),
                'otto_category/getCategories' => $urlBuilder->getUrl(
                    '*/otto_category/getCategories'
                ),
                'otto_category/getChooserEditHtml' => $urlBuilder->getUrl(
                    '*/otto_category/getChooserEditHtml'
                ),
                'otto_category/getCountsOfAttributes' => $urlBuilder->getUrl(
                    '*/otto_category/getCountsOfAttributes'
                ),
                'otto_category/getEditedCategoryInfo' => $urlBuilder->getUrl(
                    '*/otto_category/getEditedCategoryInfo'
                ),
                'otto_category/getRecent' => $urlBuilder->getUrl(
                    '*/otto_category/getRecent'
                ),
                'otto_category/getSelectedCategoryDetails' => $urlBuilder->getUrl(
                    '*/otto_category/getSelectedCategoryDetails'
                ),
                'otto_category/saveCategoryAttributes' => $urlBuilder->getUrl(
                    '*/otto_category/saveCategoryAttributes'
                ),
                'otto_category/saveCategoryDataAjax' => $urlBuilder->getUrl(
                    '*/otto_category/saveCategoryDataAjax'
                ),
            ],
        );

        $this->jsTranslator->addTranslations([
            'Select' => __('Select'),
            'Reset' => __('Reset'),
            'No recently used Categories' => __('No recently used Categories'),
            'Change Category' => __('Change Category'),
            'Edit' => __('Edit'),
            'Category' => __('Category'),
            'Not Selected' => __('Not Selected'),
            'No results' => __('No results'),
            'No saved Categories' => __('No saved Categories'),
            'Category Settings' => __('Category Settings'),
            'Specifics' => __('Specifics'),
        ]);
    }

    public function getAccountId(): int
    {
        return $this->uiListingRuntimeStorage->getListing()->getAccountId();
    }

    public function getSearchUrl(): string
    {
        return $this->getUrl('*/category/search');
    }
}
