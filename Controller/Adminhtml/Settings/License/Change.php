<?php

namespace M2E\Otto\Controller\Adminhtml\Settings\License;

class Change extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    private \M2E\Otto\Model\Config\Manager $config;
    private \M2E\Otto\Model\Servicing\Dispatcher $servicing;
    private \M2E\Otto\Helper\Module\License $licenseHelper;

    public function __construct(
        \M2E\Otto\Model\Servicing\Dispatcher $servicing,
        \M2E\Otto\Model\Config\Manager $config,
        \M2E\Otto\Helper\Module\License $licenseHelper,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->servicing = $servicing;
        $this->licenseHelper = $licenseHelper;
    }

    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPostValue();

            $key = strip_tags($post['new_license_key']);
            $this->config->setGroupValue('/license/', 'key', $key);

            try {
                $this->servicing->processTask(
                    \M2E\Otto\Model\Servicing\Task\License::NAME
                );
            } catch (\Throwable $e) {
                $this->setJsonContent([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);

                return $this->getResult();
            }

            if (
                !$this->licenseHelper->getKey() || !$this->licenseHelper->getDomain() || !$this->licenseHelper->getIp()
            ) {
                $this->setJsonContent([
                    'success' => false,
                    'message' => __('You are trying to use the unknown License Key.'),
                ]);

                return $this->getResult();
            }

            $this->setJsonContent([
                'success' => true,
                'message' => __('The License Key has been updated.'),
            ]);

            return $this->getResult();
        }

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\System\Config\Sections\License\Change::class
            )
        );

        return $this->getResult();
    }
}
