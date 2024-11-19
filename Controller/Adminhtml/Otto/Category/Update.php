<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class Update extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Dictionary\UpdateService $updateService;
    public function __construct(
        \M2E\Otto\Model\Dictionary\UpdateService $updateService
    ) {
        parent::__construct();

        $this->updateService = $updateService;
    }

    public function execute()
    {
        try {
            $this->updateService->update();

            $this->messageManager->addSuccessMessage(__(
                'Category data has been updated.',
            ));
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(__(
                'Category data failed to be updated, please try again.',
            ));
        }

        return $this->_redirect('*/otto_template_category/index');
    }
}
