<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\View;

class Configuration
{
    public const NICK = 'configuration';

    public const MODULE_AND_CHANNELS_SECTION_COMPONENT = 'otto_module_and_channels';
    public const INTERFACE_AND_MAGENTO_INVENTORY_SECTION_COMPONENT = 'otto_interface_and_magento_inventory';
    public const LOGS_CLEARING_SECTION_COMPONENT = 'otto_logs_clearing';
    public const EXTENSION_KEY_SECTION_COMPONENT = 'otto_extension_key';
    public const MIGRATION_SECTION_COMPONENT = 'otto_migration_from_magento1';

    private \Magento\Backend\Model\UrlInterface $urlBuilder;

    /**
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getLicenseUrl(array $params = []): string
    {
        return $this->urlBuilder->getUrl(
            "adminhtml/system_config/edit",
            array_merge(
                [
                    'section' => \M2E\Otto\Block\Adminhtml\System\Config\Sections::SECTION_ID_LICENSE,
                ],
                $params
            )
        );
    }
}
