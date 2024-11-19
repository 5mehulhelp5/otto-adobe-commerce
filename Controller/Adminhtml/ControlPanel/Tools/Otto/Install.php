<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Tools\Otto;

use M2E\Otto\Controller\Adminhtml\Context;
use M2E\Otto\Controller\Adminhtml\ControlPanel\AbstractCommand;
use Magento\Framework\Component\ComponentRegistrar;
use M2E\Otto\Helper\Module;

class Install extends AbstractCommand
{
    protected \Magento\Framework\Filesystem\Driver\File $filesystemDriver;

    protected \Magento\Framework\Filesystem $fileSystem;

    protected \Magento\Framework\Filesystem\File\ReadFactory $fileReaderFactory;

    protected ComponentRegistrar $componentRegistrar;

    protected \M2E\Otto\Model\ControlPanel\Inspection\Repository $repository;

    protected \M2E\Otto\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory;

    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReaderFactory,
        ComponentRegistrar $componentRegistrar,
        \M2E\Otto\Helper\View\ControlPanel $controlPanelHelper,
        Context $context,
        \M2E\Otto\Model\ControlPanel\Inspection\Repository $repository,
        \M2E\Otto\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory,
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        parent::__construct($controlPanelHelper, $context);

        $this->filesystemDriver = $filesystemDriver;
        $this->fileSystem = $filesystem;
        $this->fileReaderFactory = $fileReaderFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->repository = $repository;
        $this->handlerFactory = $handlerFactory;
        $this->serverClient = $serverClient;
    }

    public function fixColumnAction()
    {
        $repairInfo = $this->getRequest()->getPost('repair_info');

        if (empty($repairInfo)) {
            return;
        }

        foreach ($repairInfo as $item) {
            $columnsInfo[] = (array)\M2E\Otto\Helper\Json::decode($item);
        }

        $definition = $this->repository->getDefinition('TablesStructureValidity');

        /** @var  \M2E\Otto\Model\ControlPanel\Inspection\Inspector\TablesStructureValidity $inspector */
        $inspector = $this->handlerFactory->create($definition);

        foreach ($columnsInfo as $columnInfo) {
            $inspector->fix($columnInfo);
        }
    }

    public function filesDiffAction()
    {
        $filePath = base64_decode($this->getRequest()->getParam('filePath', ''));
        $originalPath = base64_decode($this->getRequest()->getParam('originalPath', ''));

        $basePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, Module::IDENTIFIER);
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $filePath;

        $params = [
            'content' => '',
            'path' => $originalPath ? $originalPath : $filePath,
        ];

        if ($this->filesystemDriver->isExists($fullPath)) {
            /** @var \Magento\Framework\Filesystem\File\Read $fileReader */
            $fileReader = $this->fileReaderFactory->create($fullPath, $this->filesystemDriver);
            $params['content'] = $fileReader->readAll();
        }

        $command = new \M2E\Otto\Model\Otto\Connector\System\Files\GetDiffCommand($params['content'], $params['path']);
        /** @var \M2E\Otto\Model\Connector\Response $response */
        $response = $this->serverClient->process($command);

        $responseData = $response->getResponseData();

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Files Difference
    <span style="color: #808080; font-size: 15px;">({$filePath})</span>
</h2>
<br/>
HTML;

        if (isset($responseData['html'])) {
            $html .= $responseData['html'];
        } else {
            $html .= '<h1>&nbsp;&nbsp;No file on server</h1>';
        }

        return $html;
    }

    /**
     * @title "Static Content Deploy"
     * @description "Static Content Deploy"
     */
    public function staticContentDeployAction()
    {
        $magentoRoot = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)
                                        ->getAbsolutePath();

        return '<pre>' . call_user_func(
            'shell_exec',
            'php ' . $magentoRoot . DIRECTORY_SEPARATOR . 'bin/magento setup:static-content:deploy'
        );
    }

    /**
     * @title "Run Magento Compilation"
     * @description "Run Magento Compilation"
     */
    public function runCompilationAction()
    {
        $magentoRoot = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)
                                        ->getAbsolutePath();

        return '<pre>' . call_user_func(
            'shell_exec',
            'php ' . $magentoRoot . DIRECTORY_SEPARATOR . 'bin/magento setup:di:compile'
        );
    }

    private function getEmptyResultsHtml($messageText)
    {
        $backUrl = $this->controlPanelHelper->getPageOwerviewTabUrl();

        return <<<HTML
<h2 style="margin: 20px 0 0 10px">
    {$messageText} <span style="color: grey; font-size: 10px;">
    <a href="{$backUrl}">[back]</a>
</h2>
HTML;
    }
}
