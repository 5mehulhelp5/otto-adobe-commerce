<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Mapping;

class Save extends \M2E\Otto\Controller\Adminhtml\AbstractMapping
{
    private \M2E\Otto\Model\AttributeMapping\GeneralService $generalService;

    public function __construct(
        \M2E\Otto\Model\AttributeMapping\GeneralService $generalService
    ) {
        parent::__construct();

        $this->generalService = $generalService;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!empty($post['general_attributes'])) {
            $this->generalService->update($post['general_attributes']);
        }

        $this->setJsonContent(
            [
                'success' => true,
            ]
        );

        return $this->getResult();
    }
}
