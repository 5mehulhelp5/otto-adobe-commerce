<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Settings\AttributeMapping;

class SetGpsrToCategory extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractSettings
{
    private \M2E\Otto\Model\AttributeMapping\GpsrService $gpsrService;

    public function __construct(
        \M2E\Otto\Model\AttributeMapping\GpsrService $gpsrService
    ) {
        parent::__construct();

        $this->gpsrService = $gpsrService;
    }

    public function execute()
    {
        try {
            $this->gpsrService->setToCategories();

            $this->setJsonContent(['success' => true]);
        } catch (\Throwable $e) {
            $this->setJsonContent(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->getResult();
    }
}
