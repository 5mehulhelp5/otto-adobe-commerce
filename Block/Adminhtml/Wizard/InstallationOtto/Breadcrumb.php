<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto;

class Breadcrumb extends \M2E\Otto\Block\Adminhtml\Widget\Breadcrumb
{
    public function _construct()
    {
        parent::_construct();

        $this->setSteps([
            [
                'id' => 'registration',
                'title' => __('Step 1'),
                'description' => __('Module Registration'),
            ],
            [
                'id' => 'account',
                'title' => __('Step 2'),
                'description' => __('Account Onboarding'),
            ],
            [
                'id' => 'settings',
                'title' => __('Step 3'),
                'description' => __('General Settings'),
            ],
            [
                'id' => 'listingTutorial',
                'title' => __('Step 4'),
                'description' => __('First Listing Creation'),
            ],
        ]);
    }
}
