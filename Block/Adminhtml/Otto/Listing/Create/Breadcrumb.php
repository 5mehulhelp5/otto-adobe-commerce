<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Create;

class Breadcrumb extends \M2E\Otto\Block\Adminhtml\Widget\Breadcrumb
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoListingBreadcrumb');

        $this->setSteps(
            [
                [
                    'id' => 1,
                    'title' => __('Step 1'),
                    'description' => __('General Settings'),
                ],
                [
                    'id' => 2,
                    'title' => __('Step 2'),
                    'description' => __('Policies'),
                ],
            ]
        );
    }
}
