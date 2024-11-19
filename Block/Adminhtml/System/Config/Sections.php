<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\System\Config;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm;

class Sections extends AbstractForm
{
    protected \Magento\Framework\View\Asset\Repository $assetRepo;

    public const SECTION_ID_MODULE_AND_CHANNELS = 'otto_module_and_channels';
    public const SECTION_ID_INTERFACE_AND_MAGENTO_INVENTORY = 'otto_interface_and_magento_inventory';
    public const SECTION_ID_LOGS_CLEARING = 'otto_logs_clearing';
    public const SECTION_ID_LICENSE = 'otto_extension_key';

    public const SELECT = \M2E\Otto\Block\Adminhtml\System\Config\Form\Element\Select::class;
    public const TEXT = \M2E\Otto\Block\Adminhtml\System\Config\Form\Element\Text::class;
    public const LINK = \M2E\Otto\Block\Adminhtml\System\Config\Form\Element\Link::class;
    public const HELP_BLOCK = \M2E\Otto\Block\Adminhtml\Magento\Form\Element\HelpBlock::class;
    public const STATE_CONTROL_BUTTON =
        \M2E\Otto\Block\Adminhtml\System\Config\Form\Element\StateControlButton::class;
    public const BUTTON = \M2E\Otto\Block\Adminhtml\System\Config\Form\Element\Button::class;
    public const NOTE = \Magento\Framework\Data\Form\Element\Note::class;

    /**
     * @param \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->assetRepo = $context->getAssetRepository();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml(): string
    {
        $generalBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\General::class);
        $asset = $this->assetRepo->createAsset("M2E_Otto::css/style.css");

        $this->js->addRequireJs(
            ['s' => 'Otto/Settings'],
            <<<JS
window.SettingsObj = new Settings();
JS
        );

        $html = <<<HTML
{$generalBlock->toHtml()}
<style>
@import url("{$asset->getUrl()}");
</style>
HTML;

        return $html . parent::_toHtml();
    }
}
