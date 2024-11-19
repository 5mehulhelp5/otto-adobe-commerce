<?php

namespace M2E\Otto\Block\Adminhtml\Listing\View;

class Header extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    protected $_template = 'listing/view/header.phtml';
    private bool $isListingViewMode = false;
    private \M2E\Otto\Helper\Magento\Store $magentoStoreHelper;
    private \M2E\Otto\Model\Listing $listing;

    public function __construct(
        \M2E\Otto\Model\Listing $listing,
        \M2E\Otto\Helper\Magento\Store $magentoStoreHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->listing = $listing;
        parent::__construct($context, $data);
    }

    public function isListingViewMode(): bool
    {
        return $this->isListingViewMode;
    }

    public function setListingViewMode($mode): self
    {
        $this->isListingViewMode = $mode;

        return $this;
    }

    public function getProfileTitle(): string
    {
        return $this->cutLongLines($this->getListing()->getTitle());
    }

    public function getAccountTitle(): string
    {
        return $this->cutLongLines($this->getListing()->getAccount()->getTitle());
    }

    public function getStoreViewBreadcrumb($cutLongValues = true)
    {
        $breadcrumb = $this->magentoStoreHelper->getStorePath($this->getListing()->getStoreId());

        return $cutLongValues ? $this->cutLongLines($breadcrumb) : $breadcrumb;
    }

    private function cutLongLines($line): string
    {
        if (strlen($line) < 50) {
            return $line;
        }

        return substr($line, 0, 50) . '...';
    }

    /**
     * @return \M2E\Otto\Model\Listing
     */
    private function getListing(): \M2E\Otto\Model\Listing
    {
        return $this->listing;
    }
}
