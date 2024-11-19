<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\System\Config\Popup;

class ModuleControlPopup extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    /** @var \M2E\Otto\Helper\Module */
    protected $moduleHelper;

    /**
     * @param \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context
     * @param \M2E\Otto\Helper\Module $moduleHelper
     * @param array $data
     */
    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\Otto\Helper\Module $moduleHelper,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        $isModuleDisabled = (int)$this->moduleHelper->isDisabled();
        if ($isModuleDisabled) {
            $confirmContent = 'Are you sure ?';
        } else {
            $confirmContent = <<<HTML
<p>In case you confirm the Module disabling, the M2E Otto Storefront dynamic tasks run by
Cron will be stopped and the M2E Otto Storefront Interface will be blocked.</p>

<p><b>Note</b>: You can re-enable it anytime you would like by clicking on the <strong>Proceed</strong>
button for <strong>Enable Module and Automatic Synchronization</strong> option.</p>
HTML;
        }

        $html = <<<HTML
<div id="module_mode_confirmation_popup" style="display: none">
    $confirmContent
</div>
HTML;

        return parent::_toHtml() . $html;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $isModuleDisabled = (int)$this->moduleHelper->isDisabled();
        if ($isModuleDisabled) {
            $title = 'Confirmation';
            $confirmBtn = 'Ok';
        } else {
            $title = 'Disable Module';
            $confirmBtn = 'Confirm';
        }

        $this->js->addRequireJs(
            ['confirm' => 'Magento_Ui/js/modal/confirm'],
            <<<JS
toggleOttoModuleStatus = function () {
    var contentHtml = jQuery('#module_mode_confirmation_popup').html();
    confirm({
        title: '$title',
        content: contentHtml,
        buttons: [{
            text: 'Cancel',
            class: 'action-secondary action-dismiss',
            click: function (event) {
                this.closeModal(event);
            }
        }, {
            text: '$confirmBtn',
            class: 'action-primary action-accept',
            click: function (event) {
                this.closeModal(event, true);
            }
        }],
        actions: {
            confirm: function () {
                toggleStatus('module_mode_field');
            },
            cancel: function () {
                return false;
            }
        }
    });
}
JS
        );
    }
}
