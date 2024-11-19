<?php

namespace M2E\Otto\Block\Adminhtml\Listing\View;

abstract class AbstractGrid extends \M2E\Otto\Block\Adminhtml\Magento\Product\Grid
{
    protected \M2E\Otto\Model\Listing $listing;
    protected \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Helper\Data\Session $sessionHelper,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        $this->listing = $data['listing'];
        parent::__construct($globalDataHelper, $sessionHelper, $context, $backendHelper, $dataHelper, $data);
    }

    public function setCollection($collection)
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->listing)) {
            $collection->setStoreId($this->listing->getStoreId());
        }

        parent::setCollection($collection);
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/view/grid.css');

        return parent::_prepareLayout();
    }

    public function getStoreId(): int
    {
        return $this->listing->getStoreId();
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setMassactionIdFieldOnlyIndexValue(bool $value): self
    {
        $this->setData('massaction_id_field_only_index_value', $value);

        return $this;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        // ---------------------------------------
        $this->jsTranslator->addTranslations([
            'Are you sure you want to create empty Listing?' => \M2E\Otto\Helper\Data::escapeJs(
                (string)__('Are you sure you want to create empty Listing?')
            ),
        ]);

        // ---------------------------------------

        return parent::_toHtml();
    }
}
