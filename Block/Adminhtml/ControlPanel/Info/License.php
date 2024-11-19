<?php

namespace M2E\Otto\Block\Adminhtml\ControlPanel\Info;

use M2E\Otto\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\Otto\Block\Adminhtml\ControlPanel\Info\License
 */
class License extends AbstractBlock
{
    private \M2E\Otto\Helper\Client $clientHelper;
    private \M2E\Otto\Helper\Data $dataHelper;
    private \M2E\Otto\Helper\Module $moduleHelper;
    private \M2E\Otto\Helper\Module\License $licenseHelper;
    /** @var array */
    public array $licenseData;
    /** @var array */
    public $locationData;

    /**
     * @param \M2E\Otto\Helper\Client $clientHelper
     * @param \M2E\Otto\Helper\Data $dataHelper
     * @param \M2E\Otto\Helper\Module $moduleHelper
     * @param \M2E\Otto\Helper\Module\License $licenseHelper
     * @param \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context
     * @param array $data
     */
    public function __construct(
        \M2E\Otto\Helper\Client $clientHelper,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Module $moduleHelper,
        \M2E\Otto\Helper\Module\License $licenseHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->clientHelper = $clientHelper;
        $this->dataHelper = $dataHelper;
        $this->moduleHelper = $moduleHelper;
        $this->licenseHelper = $licenseHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInfoLicense');
        $this->setTemplate('control_panel/info/license.phtml');
    }

    // ----------------------------------------

    protected function _beforeToHtml()
    {
        $this->licenseData = [
            'key' => \M2E\Otto\Helper\Data::escapeHtml($this->licenseHelper->getKey()),
            'domain' => \M2E\Otto\Helper\Data::escapeHtml($this->licenseHelper->getDomain()),
            'ip' => \M2E\Otto\Helper\Data::escapeHtml($this->licenseHelper->getIp()),
            'valid' => [
                'domain' => $this->licenseHelper->isValidDomain(),
                'ip' => $this->licenseHelper->isValidIp(),
            ],
        ];

        $this->locationData = [
            'domain' => $this->clientHelper->getDomain(),
            'ip' => $this->clientHelper->getIp(),
            'directory' => $this->clientHelper->getBaseDirectory(),
            'relative_directory' => $this->moduleHelper->getBaseRelativeDirectory(),
        ];

        return parent::_beforeToHtml();
    }
}
