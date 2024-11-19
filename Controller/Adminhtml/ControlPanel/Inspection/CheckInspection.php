<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\Otto\Controller\Adminhtml\Context;
use M2E\Otto\Controller\Adminhtml\ControlPanel\AbstractMain;
use M2E\Otto\Model\ControlPanel\Inspection\Repository;
use M2E\Otto\Model\ControlPanel\Inspection\Processor;

class CheckInspection extends AbstractMain
{
    /** @var \M2E\Otto\Model\ControlPanel\Inspection\Processor */
    private $processor;

    /** @var \M2E\Otto\Model\ControlPanel\Inspection\Repository */
    private $repository;

    //########################################

    public function __construct(
        Repository $repository,
        Processor $processor,
        \M2E\Otto\Model\Module $module
    ) {
        parent::__construct($module);
        $this->repository = $repository;
        $this->processor = $processor;
    }

    public function execute()
    {
        $inspectionTitle = $this->getRequest()->getParam('title');

        $definition = $this->repository->getDefinition($inspectionTitle);
        $result = $this->processor->process($definition);

        $isSuccess = true;
        $metadata = '';
        $message = __('Success');

        if ($result->isSuccess()) {
            $issues = $result->getIssues();

            if (!empty($issues)) {
                $isSuccess = false;
                $lastIssue = end($issues);

                $metadata = $lastIssue->getMetadata();
                $message = $lastIssue->getMessage();
            }
        } else {
            $message = $result->getErrorMessage();
            $isSuccess = false;
        }

        $this->setJsonContent([
            'result' => $isSuccess,
            'metadata' => $metadata,
            'message' => $message,
        ]);

        return $this->getResult();
    }
}
