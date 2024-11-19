<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto\Installation;

class ListingTutorial extends \M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto\Installation
{
    public const INSTALLATION_SKIP = 'skip';
    public const INSTALLATION_COMPLETE = 'complete';

    protected function _construct(): void
    {
        parent::_construct();

        $this->updateButton('continue', 'label', __('Create First Listing'));
        $this->updateButton('continue', 'class', 'primary');

        $completeUrl = $this->getUrl('*/*/complete', [
            'status' => self::INSTALLATION_COMPLETE,
        ]);
        $this->updateButton('continue', 'onclick', 'setLocation(\'' . $completeUrl . '\')');

        $skipUrl = $this->getUrl('*/*/complete', [
            'status' => self::INSTALLATION_SKIP,
        ]);

        $this->addButton('skip', [
            'label' => __('Skip'),
            'class' => 'primary',
            'id' => 'skip',
            'onclick' => 'setLocation(\'' . $skipUrl . '\')',
        ]);
    }

    protected function getStep(): string
    {
        return 'listingTutorial';
    }
}
